<?php

declare(strict_types=1);

use Contexis\Events\Event\Application\DTOs\OccurrenceResponse;
use Contexis\Events\Event\Domain\Enums\EventStatus;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\EventViewConfig;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use Contexis\Events\Shared\Domain\ValueObjects\AuthorId;

function eventForOccurrenceResponse(bool $detached = false): Event
{
    $timezone = new DateTimeZone('Europe/Vienna');
    $recurrenceId = $detached ? new RecurrenceId(42) : null;
    $scheduledStartsAt = new DateTimeImmutable('2026-11-10 19:00:00', $timezone);

    return new Event(
        id: new EventId(471),
        status: EventStatus::Published,
        name: 'Chorprobe',
        startDate: $scheduledStartsAt,
        endDate: $scheduledStartsAt->modify('+2 hours'),
        createdAt: new DateTimeImmutable('2026-01-01 10:00:00', $timezone),
        eventViewConfig: new EventViewConfig(),
        authorId: new AuthorId(1),
        recurrenceId: $recurrenceId,
        recurrenceOccurrenceKey: $detached ? OccurrenceKey::from($recurrenceId, $scheduledStartsAt) : null,
        isDetached: $detached,
    );
}

test('a normal event has an event-based occurrence identity', function () {
    $response = OccurrenceResponse::fromEvent(eventForOccurrenceResponse());

    expect($response->type)->toBe('real')
        ->and($response->occurrenceId)->toBe('event:471')
        ->and($response->eventId)->toBe(471)
        ->and($response->seriesId)->toBeNull();
});

test('a detached event keeps its original recurrence occurrence identity', function () {
    $response = OccurrenceResponse::fromEvent(eventForOccurrenceResponse(true));

    expect($response->type)->toBe('detached')
        ->and($response->occurrenceId)->toBe('recurrence:42:2026-11-10T19:00:00+01:00')
        ->and($response->eventId)->toBe(471)
        ->and($response->seriesId)->toBe(42);
});
