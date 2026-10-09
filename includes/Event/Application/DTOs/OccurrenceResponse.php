<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

use Contexis\Events\Event\Domain\Enums\EventStatus;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\RecurringOccurrence;
use DateTimeImmutable;

/**
 * A displayable occurrence, independent of whether it originates from a post
 * or was projected from a series rule.
 */
final readonly class OccurrenceResponse
{
    private function __construct(
        public string $occurrenceId,
        public string $type,
        public ?int $eventId,
        public ?int $seriesId,
        public string $name,
        public ?string $description,
        public ?string $audience,
        public EventStatus $status,
        public DateTimeImmutable $startDate,
        public DateTimeImmutable $endDate,
        public ?DateTimeImmutable $scheduledStartsAt,
    ) {
    }

    public static function fromEvent(Event $event): self
    {
        if ($event->isDetached) {
            return new self(
                occurrenceId: (string) $event->recurrenceOccurrenceKey,
                type: 'detached',
                eventId: $event->id->toInt(),
                seriesId: $event->recurrenceId?->toInt(),
                name: $event->name,
                description: $event->description,
                audience: $event->audience,
                status: $event->status,
                startDate: $event->startDate,
                endDate: $event->endDate,
                scheduledStartsAt: $event->recurrenceOccurrenceKey->scheduledStartsAt,
            );
        }

        return new self(
            occurrenceId: sprintf('event:%d', $event->id->toInt()),
            type: 'real',
            eventId: $event->id->toInt(),
            seriesId: null,
            name: $event->name,
            description: $event->description,
            audience: $event->audience,
            status: $event->status,
            startDate: $event->startDate,
            endDate: $event->endDate,
            scheduledStartsAt: null,
        );
    }

    public static function fromRecurringOccurrence(RecurringOccurrence $recurringOccurrence): self
    {
        return new self(
            occurrenceId: (string) $recurringOccurrence->occurrence->key,
            type: 'virtual',
            eventId: null,
            seriesId: $recurringOccurrence->recurringEvent->id->toInt(),
            name: $recurringOccurrence->recurringEvent->name,
            description: $recurringOccurrence->recurringEvent->description,
            audience: $recurringOccurrence->recurringEvent->audience,
            status: $recurringOccurrence->recurringEvent->status,
            startDate: $recurringOccurrence->occurrence->startsAt,
            endDate: $recurringOccurrence->occurrence->endsAt,
            scheduledStartsAt: $recurringOccurrence->occurrence->key->scheduledStartsAt,
        );
    }
}
