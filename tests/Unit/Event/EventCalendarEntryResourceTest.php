<?php

declare(strict_types=1);

use Contexis\Events\Event\Application\DTOs\EventCalendarEntry;
use Contexis\Events\Event\Presentation\Resources\EventCalendarEntryResource;

it('includes the draft marker in calendar entries', function (): void {
    $entry = new EventCalendarEntry(
        id: '42',
        title: 'Draft event',
        description: '',
        startDate: new DateTimeImmutable('2026-11-10 10:00:00+01:00'),
        endDate: new DateTimeImmutable('2026-11-10 11:00:00+01:00'),
        categoryIds: [],
        color: null,
        locationName: null,
        personName: null,
        isDraft: true,
    );

    expect(EventCalendarEntryResource::fromDto($entry)->toArray()['isDraft'])->toBeTrue();
});
