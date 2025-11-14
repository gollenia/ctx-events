<?php

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;
use IteratorAggregate;
use Countable;

final class TicketCollection extends Collection implements IteratorAggregate, Countable
{
    public function __construct(Ticket ...$tickets)
    {
        $this->items = $tickets;
    }

    public function getLowestPrice(): ?int
    {
        $lowest_price = null;
        foreach ($this->items as $ticket) {
            $price = $ticket->price->amount_cents;
            $lowest_price = $lowest_price === null ? $price : min($lowest_price, $price);
        }
        return $lowest_price;
    }


    public function getValidTickets(): self
    {
        $valid_tickets = array_filter($this->items, function (Ticket $ticket) {
            return $ticket->enabled === true;
        });
        return new self(...$valid_tickets);
    }
}
