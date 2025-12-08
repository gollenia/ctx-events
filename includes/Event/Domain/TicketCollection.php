<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use IteratorAggregate;
use Countable;

final class TicketCollection extends Collection implements IteratorAggregate, Countable
{
    public function __construct(Ticket ...$tickets)
    {
        $this->items = $tickets;
    }

    public function getLowestPrice(): ?Price
    {
        if (empty($this->items)) return null;

        $lowestPriceObject = null;

        foreach ($this->items as $ticket) {
            $currentPriceCents = $ticket->price->amount_cents;
            if ($lowestPriceObject === null || $currentPriceCents < $lowestPriceObject->amount_cents) {
                $lowestPriceObject = $ticket->price;
            }
        }

        return $lowestPriceObject;
    }

    public function getEnabledTickets(): self
    {
        $valid_tickets = array_filter($this->items, function (Ticket $ticket) {
            return $ticket->enabled === true;
        });
        return new self(...$valid_tickets);
    }

    public function getValidTicketsForDate(\DateTimeImmutable $now): self
    {
        $valid_tickets = array_filter($this->items, function (Ticket $ticket) use ($now) {
            return $ticket->isCurrentlyAvailable($now);
        });
        return new self(...$valid_tickets);
    }

    public function getBookableTickets(\DateTimeImmutable $now): self
    {
        return $this->getEnabledTickets()->getValidTicketsForDate($now);
    }
}
