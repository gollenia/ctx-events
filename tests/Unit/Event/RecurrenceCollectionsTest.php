<?php

declare(strict_types=1);

use Contexis\Events\Event\Domain\OccurrenceException;
use Contexis\Events\Event\Domain\OccurrenceExceptionCollection;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceIds;

test('recurrence IDs are unique and searchable', function () {
    $ids = RecurrenceIds::from(new RecurrenceId(42), new RecurrenceId(43));

    expect($ids->contains(new RecurrenceId(42)))->toBeTrue()
        ->and($ids->contains(new RecurrenceId(99)))->toBeFalse();

    expect(fn () => RecurrenceIds::from(new RecurrenceId(42), new RecurrenceId(42)))
        ->toThrow(InvalidArgumentException::class);
});

test('an occurrence exception collection rejects conflicting exceptions for one slot', function () {
    $timezone = new DateTimeZone('Europe/Vienna');
    $key = OccurrenceKey::from(new RecurrenceId(42), new DateTimeImmutable('2026-11-10 19:00:00', $timezone));
    $exceptions = OccurrenceExceptionCollection::from(OccurrenceException::cancelled($key));

    expect($exceptions->findFor($key))->not->toBeNull();

    expect(fn () => OccurrenceExceptionCollection::from(
        OccurrenceException::cancelled($key),
        OccurrenceException::cancelled($key),
    ))->toThrow(InvalidArgumentException::class);
});
