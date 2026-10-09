<?php

declare(strict_types=1);

use Contexis\Events\Event\Domain\Enums\OccurrenceState;
use Contexis\Events\Event\Domain\Enums\RecurrenceFrequency;
use Contexis\Events\Event\Domain\Enums\RecurrenceWeekday;
use Contexis\Events\Event\Domain\Occurrence;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceRule;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceWeekdays;

function viennaDate(string $date): DateTimeImmutable
{
    return new DateTimeImmutable($date, new DateTimeZone('Europe/Vienna'));
}

test('weekly recurrence rules require one or more unique weekdays', function () {
    expect(fn () => new RecurrenceRule(
        viennaDate('2026-01-06 19:00:00'),
        5400,
        new DateTimeZone('Europe/Vienna'),
        RecurrenceFrequency::Weekly,
    ))->toThrow(InvalidArgumentException::class);

    expect(fn () => new RecurrenceRule(
        viennaDate('2026-01-06 19:00:00'),
        5400,
        new DateTimeZone('Europe/Vienna'),
        RecurrenceFrequency::Weekly,
        weekdays: RecurrenceWeekdays::of(RecurrenceWeekday::Tuesday, RecurrenceWeekday::Tuesday),
    ))->toThrow(InvalidArgumentException::class);
});

test('monthly recurrence rules require exactly one unambiguous monthly rule', function () {
    expect(fn () => new RecurrenceRule(
        viennaDate('2026-01-06 19:00:00'),
        5400,
        new DateTimeZone('Europe/Vienna'),
        RecurrenceFrequency::Monthly,
    ))->toThrow(InvalidArgumentException::class);

    expect(fn () => new RecurrenceRule(
        viennaDate('2026-01-06 19:00:00'),
        5400,
        new DateTimeZone('Europe/Vienna'),
        RecurrenceFrequency::Monthly,
        weekdays: RecurrenceWeekdays::of(RecurrenceWeekday::Tuesday),
        monthday: 6,
        weekdayPosition: 1,
    ))->toThrow(InvalidArgumentException::class);
});

test('a recurrence end is an inclusive local date', function () {
    $rule = new RecurrenceRule(
        viennaDate('2026-01-06 19:00:00'),
        5400,
        new DateTimeZone('Europe/Vienna'),
        RecurrenceFrequency::Weekly,
        weekdays: RecurrenceWeekdays::of(RecurrenceWeekday::Tuesday),
        endsOn: viennaDate('2026-12-29'),
    );

    expect($rule->hasEndedBefore(viennaDate('2026-12-29 23:59:59')))->toBeFalse()
        ->and($rule->hasEndedBefore(viennaDate('2026-12-30 00:00:00')))->toBeTrue();
});

test('recurrence weekdays expose an immutable typed collection', function () {
    $weekdays = RecurrenceWeekdays::of(RecurrenceWeekday::Monday, RecurrenceWeekday::Wednesday);

    expect($weekdays->isEmpty())->toBeFalse()
        ->and($weekdays->contains(RecurrenceWeekday::Monday))->toBeTrue()
        ->and($weekdays->contains(RecurrenceWeekday::Friday))->toBeFalse()
        ->and($weekdays->count())->toBe(2);
});

test('a recurrence cannot end before its first occurrence or at a time of day', function () {
    expect(fn () => new RecurrenceRule(
        viennaDate('2026-01-06 19:00:00'),
        5400,
        new DateTimeZone('Europe/Vienna'),
        RecurrenceFrequency::Daily,
        endsOn: viennaDate('2026-01-05'),
    ))->toThrow(InvalidArgumentException::class);

    expect(fn () => new RecurrenceRule(
        viennaDate('2026-01-06 19:00:00'),
        5400,
        new DateTimeZone('Europe/Vienna'),
        RecurrenceFrequency::Daily,
        endsOn: viennaDate('2026-01-06 12:00:00'),
    ))->toThrow(InvalidArgumentException::class);
});

test('the first occurrence must use the recurrence timezone', function () {
    expect(fn () => new RecurrenceRule(
        new DateTimeImmutable('2026-01-06 18:00:00', new DateTimeZone('UTC')),
        5400,
        new DateTimeZone('Europe/Vienna'),
        RecurrenceFrequency::Daily,
    ))->toThrow(InvalidArgumentException::class);
});

test('an occurrence key identifies the planned slot, not its detached event', function () {
    $key = OccurrenceKey::from(new RecurrenceId(42), viennaDate('2026-11-10 19:00:00'));
    $occurrence = Occurrence::detached(
        $key,
        viennaDate('2026-11-11 20:00:00'),
        viennaDate('2026-11-11 21:30:00'),
        new EventId(471),
    );

    expect((string) $key)->toBe('recurrence:42:2026-11-10T19:00:00+01:00')
        ->and($occurrence->state)->toBe(OccurrenceState::Detached)
        ->and($occurrence->detachedEventId?->toInt())->toBe(471);
});

test('an occurrence validates its time range', function () {
    $key = OccurrenceKey::from(new RecurrenceId(42), viennaDate('2026-11-10 19:00:00'));

    expect(fn () => Occurrence::virtual($key, viennaDate('2026-11-10 19:00:00'), viennaDate('2026-11-10 19:00:00')))
        ->toThrow(InvalidArgumentException::class);
});
