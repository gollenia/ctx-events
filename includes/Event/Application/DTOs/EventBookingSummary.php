<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

use Contexis\Events\Booking\Domain\ValueObjects\TicketBookingsMap;
use Contexis\Events\Event\Domain\Enums\BookingDenyReason;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Contexis\Events\Shared\Domain\ValueObjects\PriceRange;

final readonly class EventBookingSummary
{
	public function __construct(
		public bool $isBookable,
		public BookingDenyReason|null $denyReason,
		public int $approved,
		public ?Price $lowestAvailablePrice,
		public ?Price $lowestPrice,
		public ?Price $highestPrice,
		public ?int $available = null,
		public ?int $totalCapacity = null,
		public ?int $pending = null,
		public ?\DateTimeImmutable $bookingStart = null,
		public ?\DateTimeImmutable $bookingEnd = null

	) {
	}

	public static function fromEvent(Event $event, \DateTimeImmutable $now, bool $isPublic, TicketBookingsMap $map): self
	{
		$bookingDecision = $event->canBookAt($now, $map);
		$priceRange = $event->tickets?->getPriceRange($now) ?? PriceRange::empty();
		$freeSpaces = $event->getFreeSpaces($now, $map);
		$canSeeSpaces = !$isPublic || $event->eventViewConfig->showFreeSpaces($freeSpaces);

		return new self(
			isBookable: $bookingDecision->allowed,
			denyReason: $bookingDecision->reason,
			approved: $map->getTotalApprovedCount(),
			pending: $map->getTotalPendingCount(),
			available: $canSeeSpaces ? $freeSpaces : null,
			totalCapacity: $event->getCapacity(),
			lowestAvailablePrice: $event->getLowestAvailablePrice($now, $map),
			lowestPrice: $priceRange->min,
			highestPrice: $priceRange->max,
			bookingStart: $event->bookingPolicy?->start(),
			bookingEnd: $event->bookingPolicy?->end()
		);
	}

	public function toArray(): array
	{
		return [
			'isBookable' => $this->isBookable,
			'denyReason' => $this->denyReason?->value,
			'approved' => $this->approved,
			'pending' => $this->pending,
			'available' => $this->available,
			'totalCapacity' => $this->totalCapacity,
			'lowestAvailablePrice' => $this->lowestAvailablePrice?->toArray(),
			'lowestPrice' => $this->lowestPrice?->toArray(),
			'highestPrice' => $this->highestPrice?->toArray(),
			'bookingStart' => $this->bookingStart?->format(DATE_ATOM),
			'bookingEnd' => $this->bookingEnd?->format(DATE_ATOM)
		];
	}
}
