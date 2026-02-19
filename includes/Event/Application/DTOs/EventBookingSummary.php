<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

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
		public int $totalBookedCount,
		public ?int $totalAvailableCount = null,
		public ?int $totalCapacity = null,
		public ?Price $lowestAvailablePrice,
		public ?Price $lowestPrice,
		public ?Price $highestPrice,
		public ?\DateTimeImmutable $bookingStart = null,
		public ?\DateTimeImmutable $bookingEnd = null

	) {
	}

	public static function fromEvent(Event $event, \DateTimeImmutable $now, bool $isPublic): self
	{
		$bookingDecision = $event->canBookAt($now);
		$priceRange = $event->tickets?->getPriceRange($now) ?? PriceRange::empty();
		$freeSpaces = $event->getFreeSpaces($now);
		$canSeeSpaces = true; //!$isPublic || $event->eventViewConfig->showFreeSpaces($freeSpaces);
		
		return new self(
			isBookable: $bookingDecision->allowed,
			denyReason: $bookingDecision->reason,
			totalBookedCount: $event->ticketBookingsMap?->getTotalBookedCount() ?? 0,
			totalAvailableCount: $canSeeSpaces ? $freeSpaces : null,
			totalCapacity: $event->overallCapacity,
			lowestAvailablePrice: $event->getLowestAvailablePrice($now),
			lowestPrice: $priceRange->min,
			highestPrice: $priceRange->max,
			bookingStart: $event->bookingPolicy->start(),
			bookingEnd: $event->bookingPolicy->end()
		);
	}

	public function toArray(): array
	{
		return [
			'isBookable' => $this->isBookable,
			'denyReason' => $this->denyReason?->value,
			'totalBookedCount' => $this->totalBookedCount,
			'totalAvailableCount' => $this->totalAvailableCount,
			'totalCapacity' => $this->totalCapacity,
			'lowestAvailablePrice' => $this->lowestAvailablePrice?->toArray(),
			'lowestPrice' => $this->lowestPrice?->toArray(),
			'highestPrice' => $this->highestPrice?->toArray(),
			'bookingStart' => $this->bookingStart?->format(DATE_ATOM),
			'bookingEnd' => $this->bookingEnd?->format(DATE_ATOM)
		];
	}
}