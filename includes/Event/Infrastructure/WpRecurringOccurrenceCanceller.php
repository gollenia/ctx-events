<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Application\Contracts\RecurringOccurrenceCanceller;
use Contexis\Events\Event\Domain\Enums\OccurrenceExceptionType;
use Contexis\Events\Event\Domain\OccurrenceGenerator;
use Contexis\Events\Event\Domain\RecurringEvent;
use Contexis\Events\Event\Domain\RecurringEventRepository;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceWindow;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use DomainException;
use RuntimeException;

/** Persists a cancelled recurring slot as an exception on its series. */
final readonly class WpRecurringOccurrenceCanceller implements RecurringOccurrenceCanceller
{
    public function __construct(
        private RecurringEventRepository $recurringEvents,
        private OccurrenceGenerator $occurrenceGenerator,
    ) {
    }

    public function cancel(RecurrenceId $recurrenceId, OccurrenceKey $occurrenceKey): void
    {
        $series = $this->recurringEvents->find($recurrenceId);
        if ($series === null) {
            throw new DomainException('Recurring event not found.');
        }

        $this->assertScheduledOccurrence($series, $occurrenceKey);
        $exceptions = $this->exceptions($recurrenceId);
        $key = (string) $occurrenceKey;

        if (isset($exceptions[$key])) {
            $this->assertCanBeCancelled($exceptions[$key]);
            return;
        }

        $exceptions[$key] = ['type' => OccurrenceExceptionType::Cancelled->value];
        if (update_post_meta($recurrenceId->toInt(), RecurringEventMeta::EXCEPTIONS, $exceptions) === false) {
            throw new RuntimeException('Could not save the cancelled occurrence exception.');
        }
    }

    private function assertScheduledOccurrence(RecurringEvent $series, OccurrenceKey $key): void
    {
        $start = $key->scheduledStartsAt->setTimezone($series->recurrenceRule->timezone);
        $window = new OccurrenceWindow($start->setTime(0, 0), $start->setTime(0, 0)->modify('+1 day'));
        foreach ($this->occurrenceGenerator->generate($series->id, $series->recurrenceRule, $window) as $occurrence) {
            if ($occurrence->key->equals($key)) {
                return;
            }
        }

        throw new DomainException('The occurrence is not scheduled by this recurring event.');
    }

    private function assertCanBeCancelled(mixed $exception): void
    {
        if (!is_array($exception) || !is_string($exception['type'] ?? null)) {
            throw new RuntimeException('Recurring event contains malformed exception metadata.');
        }
        if ($exception['type'] === OccurrenceExceptionType::Cancelled->value) {
            return;
        }
        if ($exception['type'] === OccurrenceExceptionType::Detached->value) {
            throw new DomainException('A detached occurrence cannot be cancelled from the recurring event.');
        }

        throw new RuntimeException('Recurring event contains malformed exception metadata.');
    }

    /** @return array<string, mixed> */
    private function exceptions(RecurrenceId $recurrenceId): array
    {
        $exceptions = get_post_meta($recurrenceId->toInt(), RecurringEventMeta::EXCEPTIONS, true);
        if ($exceptions === '' || $exceptions === null) {
            return [];
        }
        if (!is_array($exceptions)) {
            throw new RuntimeException('Recurring event contains malformed exception metadata.');
        }

        return $exceptions;
    }
}
