<?php

namespace Contexis\Events\Domain\Collections;

use Contexis\Events\Domain\Models\Ticket;
use IteratorAggregate;
use Countable;

final class TicketCollection extends AbstractCollection implements IteratorAggregate, Countable
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
