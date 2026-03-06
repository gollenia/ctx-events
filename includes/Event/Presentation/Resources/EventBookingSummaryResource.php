<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Presentation\Resources;

use Contexis\Events\Event\Application\DTOs\EventBookingSummary;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Contexis\Events\Shared\Presentation\Resources\PriceResource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'EventBookingSummary')]
final readonly class EventBookingSummaryResource implements Resource
{
	public function __construct(
		public bool $isBookable,
		public ?string $denyReason,
		public int $approved,
		public ?int $available,
		public ?int $pending,
		public ?int $totalCapacity,
		public ?PriceResource $lowestAvailablePrice,
		public ?PriceResource $lowestPrice,
		public ?PriceResource $highestPrice,
		public ?string $bookingStart,
		public ?string $bookingEnd
	) {
	}

	public static function from(EventBookingSummary $eventBookingSummary): self
	{
		return new self(
			isBookable: $eventBookingSummary->isBookable,
			denyReason: $eventBookingSummary->denyReason?->value,
			approved: $eventBookingSummary->approved,
			pending: $eventBookingSummary->pending,
			available: $eventBookingSummary->available,
			totalCapacity: $eventBookingSummary->totalCapacity,
			lowestAvailablePrice: $eventBookingSummary->lowestAvailablePrice ? PriceResource::from($eventBookingSummary->lowestAvailablePrice) : null,
			lowestPrice: $eventBookingSummary->lowestPrice ? PriceResource::from($eventBookingSummary->lowestPrice) : null,
			highestPrice: $eventBookingSummary->highestPrice ? PriceResource::from($eventBookingSummary->highestPrice) : null,
			bookingStart: $eventBookingSummary->bookingStart?->format(DATE_ATOM),
			bookingEnd: $eventBookingSummary->bookingEnd?->format(DATE_ATOM)
		);
	}

	public function jsonSerialize(): array
	{
		return [
			'isBookable' => $this->isBookable,
			'denyReason' => $this->denyReason,
			'approved' => $this->approved,
			'pending' => $this->pending,
			'available' => $this->available,
			'totalCapacity' => $this->totalCapacity,
			'lowestAvailablePrice' => $this->lowestAvailablePrice,
			'lowestPrice' => $this->lowestPrice,
			'highestPrice' => $this->highestPrice,
			'bookingStart' => $this->bookingStart,
			'bookingEnd' => $this->bookingEnd,
		];
	}
}

