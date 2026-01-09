<?php

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Domain\Ticket;
use Contexis\Events\Event\Domain\TicketCollection;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use DateTimeImmutable;

class TicketMapper
{
    public static function fromArray($ticket): Ticket
    {
        return new Ticket(
            id: TicketId::from($ticket['ticket_id']),
            name: $ticket['ticket_name'],
            description: $ticket['ticket_description'],
            price: Price::fromFloat($ticket['ticket_price']),
            capacity: $ticket['ticket_spaces'],
            max: $ticket['ticket_max'],
            min: $ticket['ticket_min'],
            enabled: $ticket['ticket_enabled'],
            salesStart: $ticket['ticket_start'] ? DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $ticket['ticket_start']) : null,
            salesEnd: $ticket['ticket_end'] ? DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $ticket['ticket_end']) : null,
            order: $ticket['ticket_order'],
            form: $ticket['ticket_form'],
        );
    }
}
