<?php

declare(strict_types=1);

use Contexis\Events\Event\Domain\Enums\EventStatus;
use Contexis\Events\Event\Domain\Enums\RecurrenceFrequency;
use Contexis\Events\Event\Domain\Enums\RecurrenceWeekday;
use Contexis\Events\Event\Domain\RecurringEvent;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceRule;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceWeekdays;
use Contexis\Events\Location\Domain\LocationId;
use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Person\Domain\PersonId;
use Contexis\Events\Shared\Domain\ValueObjects\AuthorId;

test('a recurring event contains shared display data and a recurrence rule', function () {
    $timezone = new DateTimeZone('Europe/Vienna');
    $rule = new RecurrenceRule(
        new DateTimeImmutable('2026-01-06 19:00:00', $timezone),
        5400,
        $timezone,
        RecurrenceFrequency::Weekly,
        weekdays: RecurrenceWeekdays::of(RecurrenceWeekday::Tuesday),
    );

    $recurringEvent = new RecurringEvent(
        new RecurrenceId(42),
        EventStatus::Published,
        'Yoga am Dienstag',
        $rule,
        new DateTimeImmutable('2025-12-01 10:00:00', $timezone),
        new AuthorId(7),
        description: 'Wöchentlicher Kurs',
        audience: 'Erwachsene',
        locationId: new LocationId(11),
        personId: new PersonId(12),
        imageId: new ImageId(13),
    );

    expect($recurringEvent->id->toInt())->toBe(42)
        ->and($recurringEvent->recurrenceRule)->toBe($rule)
        ->and($recurringEvent->locationId?->toInt())->toBe(11)
        ->and($recurringEvent->personId?->toInt())->toBe(12)
        ->and($recurringEvent->imageId?->toInt())->toBe(13);
});

test('a recurring event deliberately has no booking state', function () {
    $properties = array_column((new ReflectionClass(RecurringEvent::class))->getProperties(), 'name');

    expect($properties)->not->toContain(
        'bookingPolicy',
        'tickets',
        'overallCapacity',
        'currency',
        'forms',
    );
});
