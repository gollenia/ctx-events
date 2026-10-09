<?php

declare(strict_types=1);

use Contexis\Events\Event\Domain\Enums\RecurrenceFrequency;
use Contexis\Events\Event\Domain\Enums\RecurrenceWeekday;
use Contexis\Events\Event\Domain\OccurrenceGenerator;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceWindow;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceRule;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceWeekdays;

function occurrenceWindow(string $startsAt, string $endsAt): OccurrenceWindow
{
    $timezone = new DateTimeZone('Europe/Vienna');

    return new OccurrenceWindow(
        new DateTimeImmutable($startsAt, $timezone),
        new DateTimeImmutable($endsAt, $timezone),
    );
}

/**
 * @return list<string>
 */
function occurrenceStarts(RecurrenceRule $rule, OccurrenceWindow $window): array
{
    $occurrences = iterator_to_array(
        (new OccurrenceGenerator())->generate(new RecurrenceId(42), $rule, $window),
    );

    return array_map(
        static fn ($occurrence): string => $occurrence->startsAt->format(DATE_ATOM),
        $occurrences,
    );
}

test('generates weekly occurrences for every selected weekday', function () {
    $timezone = new DateTimeZone('Europe/Vienna');
    $rule = new RecurrenceRule(
        new DateTimeImmutable('2026-01-06 19:00:00', $timezone),
        5400,
        $timezone,
        RecurrenceFrequency::Weekly,
        weekdays: RecurrenceWeekdays::of(RecurrenceWeekday::Tuesday, RecurrenceWeekday::Thursday),
    );

    expect(occurrenceStarts($rule, occurrenceWindow('2026-01-01', '2026-01-17')))->toBe([
        '2026-01-06T19:00:00+01:00',
        '2026-01-08T19:00:00+01:00',
        '2026-01-13T19:00:00+01:00',
        '2026-01-15T19:00:00+01:00',
    ]);
});

test('skips monthly calendar days that do not exist', function () {
    $timezone = new DateTimeZone('Europe/Vienna');
    $rule = new RecurrenceRule(
        new DateTimeImmutable('2026-01-31 09:00:00', $timezone),
        3600,
        $timezone,
        RecurrenceFrequency::Monthly,
        monthday: 31,
    );

    expect(occurrenceStarts($rule, occurrenceWindow('2026-01-01', '2026-04-01')))->toBe([
        '2026-01-31T09:00:00+01:00',
        '2026-03-31T09:00:00+02:00',
    ]);
});

test('generates the last weekday of a month', function () {
    $timezone = new DateTimeZone('Europe/Vienna');
    $rule = new RecurrenceRule(
        new DateTimeImmutable('2026-01-30 18:30:00', $timezone),
        3600,
        $timezone,
        RecurrenceFrequency::Monthly,
        weekdays: RecurrenceWeekdays::of(RecurrenceWeekday::Friday),
        weekdayPosition: -1,
    );

    expect(occurrenceStarts($rule, occurrenceWindow('2026-01-01', '2026-04-01')))->toBe([
        '2026-01-30T18:30:00+01:00',
        '2026-02-27T18:30:00+01:00',
        '2026-03-27T18:30:00+01:00',
    ]);
});

test('keeps the local wall-clock time across daylight saving time', function () {
    $timezone = new DateTimeZone('Europe/Vienna');
    $rule = new RecurrenceRule(
        new DateTimeImmutable('2026-03-22 09:00:00', $timezone),
        3600,
        $timezone,
        RecurrenceFrequency::Weekly,
        weekdays: RecurrenceWeekdays::of(RecurrenceWeekday::Sunday),
    );

    expect(occurrenceStarts($rule, occurrenceWindow('2026-03-22', '2026-04-06')))->toBe([
        '2026-03-22T09:00:00+01:00',
        '2026-03-29T09:00:00+02:00',
        '2026-04-05T09:00:00+02:00',
    ]);
});

test('resolves a nonexistent daylight-saving time by moving it forward by the gap', function () {
    $timezone = new DateTimeZone('Europe/Vienna');
    $rule = new RecurrenceRule(
        new DateTimeImmutable('2026-03-22 02:30:00', $timezone),
        3600,
        $timezone,
        RecurrenceFrequency::Weekly,
        weekdays: RecurrenceWeekdays::of(RecurrenceWeekday::Sunday),
    );

    expect(occurrenceStarts($rule, occurrenceWindow('2026-03-22', '2026-04-06')))->toBe([
        '2026-03-22T02:30:00+01:00',
        '2026-03-29T03:30:00+02:00',
        '2026-04-05T02:30:00+02:00',
    ]);
});

test('resolves an ambiguous daylight-saving time to the later standard-time instant', function () {
    $timezone = new DateTimeZone('Europe/Vienna');
    $rule = new RecurrenceRule(
        new DateTimeImmutable('2026-10-11 02:30:00', $timezone),
        3600,
        $timezone,
        RecurrenceFrequency::Weekly,
        weekdays: RecurrenceWeekdays::of(RecurrenceWeekday::Sunday),
    );

    expect(occurrenceStarts($rule, occurrenceWindow('2026-10-11', '2026-11-02')))->toBe([
        '2026-10-11T02:30:00+02:00',
        '2026-10-18T02:30:00+02:00',
        '2026-10-25T02:30:00+01:00',
        '2026-11-01T02:30:00+01:00',
    ]);
});

test('respects an inclusive recurrence end date and an exclusive query window end', function () {
    $timezone = new DateTimeZone('Europe/Vienna');
    $rule = new RecurrenceRule(
        new DateTimeImmutable('2026-01-06 19:00:00', $timezone),
        5400,
        $timezone,
        RecurrenceFrequency::Weekly,
        weekdays: RecurrenceWeekdays::of(RecurrenceWeekday::Tuesday),
        endsOn: new DateTimeImmutable('2026-01-13', $timezone),
    );

    expect(occurrenceStarts($rule, occurrenceWindow('2026-01-01', '2026-01-13 19:00:00')))->toBe([
        '2026-01-06T19:00:00+01:00',
    ]);

    expect(occurrenceStarts($rule, occurrenceWindow('2026-01-01', '2026-01-14')))->toBe([
        '2026-01-06T19:00:00+01:00',
        '2026-01-13T19:00:00+01:00',
    ]);
});

test('rejects occurrence windows longer than twelve months', function () {
    expect(fn () => occurrenceWindow('2026-01-01', '2027-01-02'))
        ->toThrow(InvalidArgumentException::class);
});
