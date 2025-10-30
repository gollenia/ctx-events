<?php

namespace Contexis\Events\Domain\ValueObjects;

class PriceSummary {
	public function __construct(
		public readonly Price $base_price,
		public readonly Price $donation_amount,
		public readonly Price $discount_amount,
		public readonly Price $total_price
	) {}

	public function is_free(): bool {
		return $this->total_price->is_free();
	}

	public function from_values(
		int $base_price,
		int $donation_amount,
		int $discount_amount,
		string $currency
	): self {
		$total_price = max(0, $base_price + $donation_amount - $discount_amount);

		return new self(
			new Price($base_price, $currency),
			new Price($donation_amount, $currency),
			new Price($discount_amount, $currency),
			new Price($total_price, $currency)
		);
	}
}