<?php

declare(strict_types=1);

use Contexis\Events\Event\Application\Service\OccurrenceWindowFactory;
use Contexis\Events\Event\Domain\Enums\TimeScope;

function occurrenceFactoryNow(): DateTimeImmutable
{
    return new DateTimeImmutable('2026-03-04 10:30:00', new DateTimeZone('Europe/Vienna'));
}

test('the future occurrence scope is a rolling twelve-month window', function () {
    $window = (new OccurrenceWindowFactory())->fromScope(TimeScope::FUTURE, occurrenceFactoryNow());

    expect($window->startsAt->format(DATE_ATOM))->toBe('2026-03-04T10:30:00+01:00')
        ->and($window->endsAt->format(DATE_ATOM))->toBe('2027-03-04T10:30:00+01:00');
});

test('calendar-oriented scopes resolve to complete local calendar periods', function (TimeScope $scope, string $startsAt, string $endsAt) {
    $window = (new OccurrenceWindowFactory())->fromScope($scope, occurrenceFactoryNow());

    expect($window->startsAt->format(DATE_ATOM))->toBe($startsAt)
        ->and($window->endsAt->format(DATE_ATOM))->toBe($endsAt);
})->with([
    'today' => [TimeScope::TODAY, '2026-03-04T00:00:00+01:00', '2026-03-05T00:00:00+01:00'],
    'this week' => [TimeScope::THIS_WEEK, '2026-03-02T00:00:00+01:00', '2026-03-09T00:00:00+01:00'],
    'this month' => [TimeScope::THIS_MONTH, '2026-03-01T00:00:00+01:00', '2026-04-01T00:00:00+02:00'],
    'next month' => [TimeScope::NEXT_MONTH, '2026-04-01T00:00:00+02:00', '2026-05-01T00:00:00+02:00'],
    'this year' => [TimeScope::THIS_YEAR, '2026-01-01T00:00:00+01:00', '2027-01-01T00:00:00+01:00'],
]);

test('unbounded occurrence scopes are rejected', function (TimeScope $scope) {
    expect(fn () => (new OccurrenceWindowFactory())->fromScope($scope, occurrenceFactoryNow()))
        ->toThrow(InvalidArgumentException::class);
})->with([TimeScope::ALL, TimeScope::PAST]);
