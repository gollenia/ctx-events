<?php

namespace Contexis\Events\Shared\Presentation\Resources;

use Contexis\Events\Shared\Domain\ValueObjects\Price;

final readonly class PriceResource
{
	public function __construct(
		public int $amountCents,
		public string $currency,
	) {
	}

	public static function from(Price $price): self
	{
		return new self(
			amountCents: $price->amountCents,
			currency: $price->currency->toString(),
		);
	}
}