<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain\ValueObjects;

use Contexis\Events\Event\Domain\Enums\RecurrenceFrequency;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

/**
 * An immutable rule for generating the scheduled slots of a recurring event.
 *
 * `endsOn` is a local calendar date, represented as midnight in the recurrence
 * timezone. A slot may start on that date; it need not end on that date.
 */
final readonly class RecurrenceRule
{
    public RecurrenceWeekdays $weekdays;

    public function __construct(
        public DateTimeImmutable $firstOccurrenceStartsAt,
        public int $occurrenceDurationSeconds,
        public DateTimeZone $timezone,
        public RecurrenceFrequency $frequency,
        public int $interval = 1,
        ?RecurrenceWeekdays $weekdays = null,
        public ?int $monthday = null,
        public ?int $weekdayPosition = null,
        public ?DateTimeImmutable $endsOn = null,
    ) {
        $this->weekdays = $weekdays ?? RecurrenceWeekdays::empty();

        if ($this->occurrenceDurationSeconds <= 0) {
            throw new InvalidArgumentException('An occurrence duration must be greater than zero.');
        }

        if ($this->interval < 1) {
            throw new InvalidArgumentException('A recurrence interval must be at least one.');
        }

        $this->assertFirstOccurrenceUsesTimezone();
        $this->assertEndsOnIsLocalDate();
        $this->assertFrequencySpecificFields();
    }

    public function hasEndedBefore(DateTimeImmutable $localDate): bool
    {
        if ($this->endsOn === null) {
            return false;
        }

        return $localDate->setTimezone($this->timezone)->format('Y-m-d')
            > $this->endsOn->format('Y-m-d');
    }

    private function assertFirstOccurrenceUsesTimezone(): void
    {
        if ($this->firstOccurrenceStartsAt->getTimezone()->getName() !== $this->timezone->getName()) {
            throw new InvalidArgumentException('The first occurrence must use the recurrence timezone.');
        }
    }

    private function assertEndsOnIsLocalDate(): void
    {
        if ($this->endsOn === null) {
            return;
        }

        if ($this->endsOn->getTimezone()->getName() !== $this->timezone->getName()
            || $this->endsOn->format('H:i:s') !== '00:00:00') {
            throw new InvalidArgumentException('A recurrence end must be a midnight date in the recurrence timezone.');
        }

        if ($this->endsOn->format('Y-m-d') < $this->firstOccurrenceStartsAt->setTimezone($this->timezone)->format('Y-m-d')) {
            throw new InvalidArgumentException('A recurrence cannot end before its first occurrence.');
        }
    }

    private function assertFrequencySpecificFields(): void
    {
        match ($this->frequency) {
            RecurrenceFrequency::Daily, RecurrenceFrequency::Yearly => $this->assertNoFrequencyDetails(),
            RecurrenceFrequency::Weekly => $this->assertWeeklyDetails(),
            RecurrenceFrequency::Monthly => $this->assertMonthlyDetails(),
        };
    }

    private function assertNoFrequencyDetails(): void
    {
        if (!$this->weekdays->isEmpty() || $this->monthday !== null || $this->weekdayPosition !== null) {
            throw new InvalidArgumentException('This recurrence frequency does not accept weekday or monthly details.');
        }
    }

    private function assertWeeklyDetails(): void
    {
        if ($this->weekdays->isEmpty()) {
            throw new InvalidArgumentException('A weekly recurrence requires at least one weekday.');
        }

        if ($this->monthday !== null || $this->weekdayPosition !== null) {
            throw new InvalidArgumentException('A weekly recurrence does not accept monthly details.');
        }
    }

    private function assertMonthlyDetails(): void
    {
        if (($this->monthday === null) === ($this->weekdayPosition === null)) {
            throw new InvalidArgumentException('A monthly recurrence requires exactly one monthly rule.');
        }

        if ($this->monthday !== null) {
            if ($this->monthday < 1 || $this->monthday > 31 || !$this->weekdays->isEmpty()) {
                throw new InvalidArgumentException('A monthly day rule requires a day from one to thirty-one and no weekday.');
            }

            return;
        }

        if (!in_array($this->weekdayPosition, [-1, 1, 2, 3, 4, 5], true) || $this->weekdays->count() !== 1) {
            throw new InvalidArgumentException('A monthly weekday rule requires one weekday and a position from first to fifth or last.');
        }
    }
}
