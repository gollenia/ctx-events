<?php

declare(strict_types=1);

use Contexis\Events\Event\Domain\Enums\OccurrenceExceptionType;
use Contexis\Events\Event\Domain\OccurrenceException;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;

function occurrenceExceptionKey(): OccurrenceKey
{
    return OccurrenceKey::from(
        new RecurrenceId(42),
        new DateTimeImmutable('2026-11-10 19:00:00', new DateTimeZone('Europe/Vienna')),
    );
}

test('a cancelled occurrence exception has no detached event', function () {
    $exception = OccurrenceException::cancelled(occurrenceExceptionKey());

    expect($exception->type)->toBe(OccurrenceExceptionType::Cancelled)
        ->and($exception->detachedEventId)->toBeNull();
});

test('a detached occurrence exception references its individual event', function () {
    $exception = OccurrenceException::detached(occurrenceExceptionKey(), new EventId(471));

    expect($exception->type)->toBe(OccurrenceExceptionType::Detached)
        ->and($exception->detachedEventId?->toInt())->toBe(471);
});

test('an occurrence key round-trips through its persisted representation', function () {
    $key = occurrenceExceptionKey();

    expect(OccurrenceKey::fromString((string) $key)->equals($key))->toBeTrue();
});
