<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Presentation\Resources;

use Contexis\Events\Shared\Domain\ValueObjects\Price;

final readonly class TicketResource
{
	public function __construct(
		public string $id,
		public string $name,
		public ?string $description,
		public Price $price,
		public ?int $capacity,
		public ?bool $enabled,
		public ?string $salesStart,
		public ?string $salesEnd,
		public int $order,
		public int $min,
		public int $max,
	) {
	}
}