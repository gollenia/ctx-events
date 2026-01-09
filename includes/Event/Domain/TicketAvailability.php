<?php
declare(strict_types = 1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

final class TicketAvailability
{

	public function __construct(
		private readonly Ticket $ticket,
		private readonly int $booked,
		private readonly Clock $clock
	) {
	}

	public function isAvailable(): bool
	{
		if (!$this->ticket->isBookable($this->clock->now())) return false;
		if ($this->ticket->capacity === null) return true;
		return $this->ticket->capacity > $this->booked;
	}

	public function getRemainingSpaces(): int
    {
		if($this->ticket->capacity === null) return PHP_INT_MAX;
        return max(0, $this->ticket->capacity - $this->booked);
    }

	public function getBookedSpaces(): int
    {
        return $this->booked;
    }

	public function isSoldOut(): bool
    {
        return $this->getRemainingSpaces() === 0;
    }

	public function getTicketPrice(): Price
    {
        return $this->ticket->price;
    }
}