<?php

declare(strict_types=1);

use Contexis\Events\Event\Domain\Enums\EventStatus;
use Contexis\Events\Event\Domain\OccurrenceCriteria;
use Contexis\Events\Event\Domain\ValueObjects\EventFilters;
use Contexis\Events\Event\Domain\ValueObjects\EventStatusList;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceWindow;

test('occurrence criteria combines a finite window, shared filters and event visibility', function () {
    $timezone = new DateTimeZone('Europe/Vienna');
    $criteria = new OccurrenceCriteria(
        new OccurrenceWindow(
            new DateTimeImmutable('2026-01-01', $timezone),
            new DateTimeImmutable('2026-02-01', $timezone),
        ),
        new EventFilters(categories: [1], locationId: 2, search: 'Yoga'),
        EventStatusList::public(),
    );

    expect($criteria->filters->locationId)->toBe(2)
        ->and($criteria->statuses->contains(EventStatus::Published))->toBeTrue()
        ->and($criteria->statuses->contains(EventStatus::Draft))->toBeFalse();
});
