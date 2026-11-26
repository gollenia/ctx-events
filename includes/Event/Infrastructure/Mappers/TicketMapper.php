<?php

namespace Contexis\Events\Event\Infrastructure\Mappers;

use Contexis\Events\Event\Domain\Ticket;
use Contexis\Events\Event\Domain\TicketCollection;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use DateTimeImmutable;
use DateTimeZone;

class TicketMapper
{
    /** @param array<string, mixed> $ticket */
    public static function fromArray(array $ticket, Currency $currency, ?DateTimeZone $timezone = null): Ticket
    {
        $start = $ticket['ticket_start'] ?? null;
        $end = $ticket['ticket_end'] ?? null;
        $salesStart = self::parseSalesDate($start, $timezone);
        $salesEnd = self::parseSalesDate($end, $timezone, endOfDay: true);
        $invalidSalesWindow = ($salesStart === null && self::hasSalesDate($start))
            || ($salesEnd === null && self::hasSalesDate($end));

        return new Ticket(
            id: TicketId::from($ticket['ticket_id']),
            name: $ticket['ticket_name'],
            description: $ticket['ticket_description'],
            price: Price::from($ticket['ticket_price'], $currency),
            capacity: self::normalizeCapacity($ticket['ticket_spaces'] ?? null),
            max: $ticket['ticket_max'],
            min: $ticket['ticket_min'],
            enabled: $invalidSalesWindow ? false : $ticket['ticket_enabled'],
            salesStart: $salesStart,
            salesEnd: $salesEnd,
            order: $ticket['ticket_order'],
            form: $ticket['ticket_form'],
        );
    }

    private static function hasSalesDate(mixed $value): bool
    {
        return $value !== null && $value !== false && (!is_string($value) || trim($value) !== '');
    }

    private static function parseSalesDate(mixed $value, ?DateTimeZone $timezone, bool $endOfDay = false): ?DateTimeImmutable
    {
        if (!is_string($value) || str_contains($value, "\0")) {
            return null;
        }

        $value = trim($value);
        $dateOnly = strlen($value) === 10;
        $format = $dateOnly ? 'Y-m-d' : 'Y-m-d H:i:s';
        $date = DateTimeImmutable::createFromFormat('!' . $format, $value, $timezone);
        if ($date === false || $date->format($format) !== $value) {
            return null;
        }

        return $dateOnly && $endOfDay ? $date->setTime(23, 59, 59, 999999) : $date;
    }

    private static function normalizeCapacity(mixed $capacity): ?int
    {
        if ($capacity === null || $capacity === '') {
            return null;
        }

        return is_numeric($capacity) ? (int) $capacity : null;
    }
}
