<?php

declare(strict_types=1);

use Contexis\Events\Event\Application\Service\IcalEventCalendarExporter;
use Tests\Support\FakeEventFactory;

test('exports an event as an ical file', function () {
    $event = FakeEventFactory::create(501);
    $exporter = new IcalEventCalendarExporter();

    $calendar = $exporter->export($event);

    expect($calendar->filename)->toEndWith('.ics');
    expect($calendar->mimeType)->toBe('text/calendar; charset=UTF-8');
    expect($calendar->content)->toContain(
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'BEGIN:VEVENT',
        'SUMMARY:',
        'DTSTART:',
        'DTEND:',
        'END:VCALENDAR',
    );
});
