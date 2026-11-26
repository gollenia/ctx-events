<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\Mappers\TicketMapper;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;

it('maps stored ticket sales dates without interrupting event rendering', function (
    array $salesDates,
    ?string $start,
    ?string $end,
    bool $available,
): void {
    $ticket = TicketMapper::fromArray([
        'ticket_id' => 'standard',
        'ticket_name' => 'Standard',
        'ticket_description' => '',
        'ticket_price' => 0,
        'ticket_spaces' => 50,
        'ticket_max' => 0,
        'ticket_min' => 0,
        'ticket_enabled' => true,
        'ticket_order' => 0,
        'ticket_form' => 0,
        ...$salesDates,
    ], Currency::fromCode('EUR'), new DateTimeZone('Europe/Vienna'));

    expect($ticket->salesStart?->format('Y-m-d H:i:s P'))->toBe($start)
        ->and($ticket->salesEnd?->format('Y-m-d H:i:s P'))->toBe($end)
        ->and($ticket->isBookable(new DateTimeImmutable('2026-11-26 12:00:00 Europe/Vienna')))->toBe($available);
})->with([
    'date-only bounds' => [
        ['ticket_start' => '2026-11-26', 'ticket_end' => '2026-11-26'],
        '2026-11-26 00:00:00 +01:00', '2026-11-26 23:59:59 +01:00', true,
    ],
    'full timestamp' => [
        ['ticket_start' => '2026-11-26 15:30:00'],
        '2026-11-26 15:30:00 +01:00', null, false,
    ],
    'unset bounds' => [['ticket_end' => ''], null, null, true],
    'invalid start' => [['ticket_start' => '2026-02-30'], null, null, false],
    'invalid end' => [['ticket_end' => "invalid\0date"], null, null, false],
]);
