<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\Service;

use Contexis\Events\Event\Application\Contracts\EventCalendarExporter;
use Contexis\Events\Event\Application\DTOs\EventCalendarFile;
use Contexis\Events\Event\Domain\Event;

final class IcalEventCalendarExporter implements EventCalendarExporter
{
    public function export(Event $event): EventCalendarFile
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Contexis//Events//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:' . $this->escapeText(sprintf('ctx-events-%d', $event->id->toInt())),
            'DTSTAMP:' . $this->formatDateTimeUtc($event->createdAt),
            'DTSTART:' . $this->formatDateTimeUtc($event->startDate),
            'DTEND:' . $this->formatDateTimeUtc($event->endDate),
            'SUMMARY:' . $this->escapeText($event->name),
        ];

        if ($event->description !== null && trim($event->description) !== '') {
            $lines[] = 'DESCRIPTION:' . $this->escapeText($event->description);
        }

        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        return new EventCalendarFile(
            filename: $this->filename($event),
            mimeType: 'text/calendar; charset=UTF-8',
            content: implode("\r\n", $lines) . "\r\n",
        );
    }

    private function formatDateTimeUtc(\DateTimeImmutable $value): string
    {
        return $value
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format('Ymd\THis\Z');
    }

    private function escapeText(string $value): string
    {
        return str_replace(
            ["\\", ";", ",", "\r\n", "\r", "\n"],
            ["\\\\", "\;", "\,", '\n', '\n', '\n'],
            $value,
        );
    }

    private function filename(Event $event): string
    {
        $slug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $event->name), '-'));
        $slug = $slug !== '' ? $slug : 'event-' . $event->id->toInt();

        return $slug . '.ics';
    }
}
