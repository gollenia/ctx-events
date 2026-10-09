<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Event\Domain\Enums\RecurrenceFrequency;
use Contexis\Events\Event\Domain\Enums\RecurrenceWeekday;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceWindow;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceRule;
use DateTimeImmutable;
use Generator;

/**
 * Projects virtual occurrence starts inside an end-exclusive window.
 */
final class OccurrenceGenerator
{
    /**
     * @return Generator<int, Occurrence>
     */
    public function generate(
        RecurrenceId $recurringEventId,
        RecurrenceRule $rule,
        OccurrenceWindow $window,
    ): Generator {
        $firstOccurrence = $rule->firstOccurrenceStartsAt->setTimezone($rule->timezone);
        $windowStartsAt = $window->startsAt->setTimezone($rule->timezone);
        $windowEndsAt = $window->endsAt->setTimezone($rule->timezone);

        $cursor = $this->startOfLocalDay($windowStartsAt);
        $firstDate = $this->startOfLocalDay($firstOccurrence);

        if ($cursor < $firstDate) {
            $cursor = $firstDate;
        }

        while ($cursor < $windowEndsAt) {
            $startsAt = $this->atOccurrenceTime($cursor, $firstOccurrence);

            if ($startsAt >= $windowEndsAt) {
                break;
            }

            if ($startsAt >= $windowStartsAt
                && !$rule->hasEndedBefore($startsAt)
                && $this->isScheduledOn($cursor, $firstDate, $rule)) {
                yield Occurrence::virtual(
                    OccurrenceKey::from($recurringEventId, $startsAt),
                    $startsAt,
                    $this->addSeconds($startsAt, $rule->occurrenceDurationSeconds),
                );
            }

            $cursor = $cursor->modify('+1 day');
        }
    }

    private function isScheduledOn(
        DateTimeImmutable $date,
        DateTimeImmutable $firstDate,
        RecurrenceRule $rule,
    ): bool {
        return match ($rule->frequency) {
            RecurrenceFrequency::Daily => $this->daysBetween($firstDate, $date) % $rule->interval === 0,
            RecurrenceFrequency::Weekly => $this->isScheduledWeekly($date, $firstDate, $rule),
            RecurrenceFrequency::Monthly => $this->isScheduledMonthly($date, $firstDate, $rule),
            RecurrenceFrequency::Yearly => $this->isScheduledYearly($date, $firstDate, $rule),
        };
    }

    private function isScheduledWeekly(DateTimeImmutable $date, DateTimeImmutable $firstDate, RecurrenceRule $rule): bool
    {
        $firstWeek = $firstDate->modify('monday this week');
        $currentWeek = $date->modify('monday this week');
        $weeksBetween = intdiv($this->daysBetween($firstWeek, $currentWeek), 7);
        $weekday = RecurrenceWeekday::from(strtolower($date->format('l')));

        return $weeksBetween % $rule->interval === 0 && $rule->weekdays->contains($weekday);
    }

    private function isScheduledMonthly(DateTimeImmutable $date, DateTimeImmutable $firstDate, RecurrenceRule $rule): bool
    {
        $monthsBetween = ((int) $date->format('Y') - (int) $firstDate->format('Y')) * 12
            + (int) $date->format('n') - (int) $firstDate->format('n');

        if ($monthsBetween % $rule->interval !== 0) {
            return false;
        }

        if ($rule->monthday !== null) {
            return (int) $date->format('j') === $rule->monthday;
        }

        $weekday = RecurrenceWeekday::from(strtolower($date->format('l')));
        if (!$rule->weekdays->contains($weekday)) {
            return false;
        }

        if ($rule->weekdayPosition === -1) {
            return $date->modify('+7 days')->format('n') !== $date->format('n');
        }

        return intdiv((int) $date->format('j') - 1, 7) + 1 === $rule->weekdayPosition;
    }

    private function isScheduledYearly(DateTimeImmutable $date, DateTimeImmutable $firstDate, RecurrenceRule $rule): bool
    {
        $yearsBetween = (int) $date->format('Y') - (int) $firstDate->format('Y');

        return $yearsBetween % $rule->interval === 0
            && $date->format('m-d') === $firstDate->format('m-d');
    }

    private function startOfLocalDay(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date->setTime(0, 0, 0);
    }

    /**
     * Resolves local daylight-saving anomalies consistently: a nonexistent
     * spring time moves forward by the size of the gap (02:30 becomes 03:30),
     * while an ambiguous autumn time uses the later, standard-time instant.
     */
    private function atOccurrenceTime(DateTimeImmutable $date, DateTimeImmutable $firstOccurrence): DateTimeImmutable
    {
        $localDateTime = $date->setTime(
            (int) $firstOccurrence->format('H'),
            (int) $firstOccurrence->format('i'),
            (int) $firstOccurrence->format('s'),
        );

        return $this->laterAmbiguousInstant($localDateTime);
    }

    /**
     * PHP can resolve an ambiguous local time differently depending on how the
     * DateTimeImmutable was constructed. Select the later (post-transition)
     * instant ourselves so the recurrence policy is deterministic.
     */
    private function laterAmbiguousInstant(DateTimeImmutable $localDateTime): DateTimeImmutable
    {
        $timezone = $localDateTime->getTimezone();
        $transitions = $timezone->getTransitions(
            $localDateTime->getTimestamp() - 86_400,
            $localDateTime->getTimestamp() + 86_400,
        );

        if ($transitions === false || count($transitions) < 2) {
            return $localDateTime;
        }

        $localTimestamp = gmmktime(
            (int) $localDateTime->format('H'),
            (int) $localDateTime->format('i'),
            (int) $localDateTime->format('s'),
            (int) $localDateTime->format('n'),
            (int) $localDateTime->format('j'),
            (int) $localDateTime->format('Y'),
        );

        for ($index = 1, $count = count($transitions); $index < $count; $index++) {
            $beforeOffset = $transitions[$index - 1]['offset'];
            $afterOffset = $transitions[$index]['offset'];
            $transitionTimestamp = $transitions[$index]['ts'];

            if ($afterOffset >= $beforeOffset) {
                continue;
            }

            $ambiguousStartsAt = $transitionTimestamp + $afterOffset;
            $ambiguousEndsAt = $transitionTimestamp + $beforeOffset;

            if ($localTimestamp >= $ambiguousStartsAt && $localTimestamp < $ambiguousEndsAt) {
                return (new DateTimeImmutable('@' . ($localTimestamp - $afterOffset)))
                    ->setTimezone($timezone);
            }
        }

        return $localDateTime;
    }

    private function addSeconds(DateTimeImmutable $date, int $seconds): DateTimeImmutable
    {
        return $date->setTimestamp($date->getTimestamp() + $seconds);
    }

    private function daysBetween(DateTimeImmutable $from, DateTimeImmutable $to): int
    {
        return (int) $from->diff($to)->format('%a');
    }
}
