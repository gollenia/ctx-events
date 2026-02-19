<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Presentation\Resources;

use Contexis\Events\Event\Application\DTOs\EventBookingSummary;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'EventBookingSummary')]
final readonly class EventBookingSummaryResource implements Resource
{
	public function __construct(
		public bool $isBookable,
		public ?string $denyReason,
		public int $totalBookedCount,
		public ?int $totalAvailableCount,
		public ?int $totalCapacity,
		public ?Price $lowestAvailablePrice,
		public ?Price $lowestPrice,
		public ?Price $highestPrice,
		public ?string $bookingStart,
		public ?string $bookingEnd
	) {
	}

	public static function from(EventBookingSummary $eventBookingSummary): self
	{
		return new self(
			isBookable: $eventBookingSummary->isBookable,
			denyReason: $eventBookingSummary->denyReason?->value,
			totalBookedCount: $eventBookingSummary->totalBookedCount,
			totalAvailableCount: $eventBookingSummary->totalAvailableCount,
			totalCapacity: $eventBookingSummary->totalCapacity,
			lowestAvailablePrice: $eventBookingSummary->lowestAvailablePrice,
			lowestPrice: $eventBookingSummary->lowestPrice,
			highestPrice: $eventBookingSummary->highestPrice,
			bookingStart: $eventBookingSummary->bookingStart?->format(DATE_ATOM),
			bookingEnd: $eventBookingSummary->bookingEnd?->format(DATE_ATOM)
		);
	}

	public function jsonSerialize(): array
	{
		return [
			'isBookable' => $this->isBookable,
			'denyReason' => $this->denyReason,
			'totalBookedCount' => $this->totalBookedCount,
			'totalAvailableCount' => $this->totalAvailableCount,
			'totalCapacity' => $this->totalCapacity,
			'lowestAvailablePrice' => $this->lowestAvailablePrice,
			'lowestPrice' => $this->lowestPrice,
			'highestPrice' => $this->highestPrice,
			'bookingStart' => $this->bookingStart,
			'bookingEnd' => $this->bookingEnd,
		];
	}
}

