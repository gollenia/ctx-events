<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Booking\Domain\ValueObjects\TicketBookingsMap;
use Contexis\Events\Booking\Domain\ValueObjects\TicketSalesStats;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Shared\Domain\Abstract\Collection;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Contexis\Events\Shared\Domain\ValueObjects\PriceRange;
use IteratorAggregate;
use Countable;

final readonly class TicketCollection extends Collection
{
    public static function from(Ticket ...$tickets): self
    {
        return new self($tickets);
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
		return self::from(...$availableTickets);
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
        return self::from(...$valid_tickets);
    }

	public function getValidTicketsForDate(\DateTimeImmutable $now): self
    {
        $valid_tickets = array_filter($this->items, function (Ticket $ticket) use ($now) {
            return $ticket->isCurrentlyAvailable($now);
        });
        return self::from(...$valid_tickets);
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
				return null;
			}
			$soldCount = $map->getStatsFor($ticket->id)->getBookedCount();
			$freeSpaces = max(0, $ticket->capacity - $soldCount);
			$totalFreeSpaces += $freeSpaces;
		}
		return $totalFreeSpaces;
	}

	public function getTicketIds(): array
	{
		return array_map(fn (Ticket $ticket): string => $ticket->id->toString(), $this->items);
	}

	public function getCapacity(): ?int
	{
		$totalCapacity = 0;
		foreach ($this->items as $ticket) {
			if ($ticket->capacity === null) {
				return null;
			}
			$totalCapacity += $ticket->capacity;
		}
		return $totalCapacity;
	}

	public function getTicketById(TicketId $id): Ticket
	{
		foreach ($this->items as $ticket) {
			if ($ticket->id->equals($id)) {
				return $ticket;
			}
		}
		throw new \DomainException("Ticket not found: {$id->toString()}");
	}

	public function getFreeSpacesForTicket(TicketId $id, TicketBookingsMap $map): ?int
	{
		$ticket = $this->getTicketById($id);
		if ($ticket->capacity === null) {
			return null;
		}
		$soldCount = $map->getStatsFor($ticket->id)->getBookedCount();
		return max(0, $ticket->capacity - $soldCount);
	}
}
