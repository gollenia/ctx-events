<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Booking\Domain\ValueObjects\TicketBookingsMap;
use Contexis\Events\Booking\Domain\ValueObjects\TicketSalesStats;
use Contexis\Events\Shared\Domain\Abstract\Collection;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Contexis\Events\Shared\Domain\ValueObjects\PriceRange;
use IteratorAggregate;
use Countable;

final class TicketCollection extends Collection implements IteratorAggregate, Countable
{
    public function __construct(Ticket ...$tickets)
    {
        $this->items = $tickets;
    }

	public function getBookableTickets(TicketBookingsMap $map, \DateTimeImmutable $now): self
	{
		return $this
        ->getEnabledTickets()
        ->getValidTicketsForDate($now)
        ->getAvailableTickets($map);
	}

	public function getAvailableTickets(TicketBookingsMap $map): self{
		$availableTickets = [];
		foreach ($this->items as $ticket) {
			$soldCount = $map->getStatsFor($ticket->id)->getBookedCount();
			if ($ticket->capacity === null || $soldCount < $ticket->capacity) {
				$availableTickets[] = $ticket;
			}
		}
		return new self(...$availableTickets);
	}

	public function hasAvailableTickets(TicketBookingsMap $map): bool
	{
		foreach ($this->items as $ticket) {
			$soldCount = $map->getStatsFor($ticket->id)->getBookedCount();
			if ($ticket->capacity === null || $soldCount < $ticket->capacity) {
				return true;
			}
		}
		return false;
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

    public function getLowestAvailablePrice(\DateTimeImmutable $now): ?Price
    {
        if (empty($this->items)) return null;

        $lowestPriceObject = null;

        foreach ($this->items as $ticket) {
			if(!$ticket->isCurrentlyAvailable($now)) continue;
            $currentPriceCents = $ticket->price->amountCents;
            if ($lowestPriceObject === null || $currentPriceCents < $lowestPriceObject->amountCents) {
                $lowestPriceObject = $ticket->price;
            }
        }

        return $lowestPriceObject;
    }

	public function getPriceRange(\DateTimeImmutable $now): PriceRange
	{
		$prices = [];
		foreach ($this->items as $ticket) {
			if(!$ticket->isCurrentlyAvailable($now)) continue;
			$prices[] = $ticket->price;
		}
		if (empty($prices)) {
			return PriceRange::empty();
		}
		return PriceRange::fromPrices(...$prices);
	}

	public function getFreeSpaces(TicketBookingsMap $map): ?int
	{
		$totalFreeSpaces = 0;
		foreach ($this->items as $ticket) {
			if ($ticket->capacity === null) {
				return null; // Unlimited capacity, so we return null to indicate this
			}
			$soldCount = $map->getStatsFor($ticket->id)->getBookedCount();
			$freeSpaces = max(0, $ticket->capacity - $soldCount);
			$totalFreeSpaces += $freeSpaces;
		}
		return $totalFreeSpaces;
	}
}
