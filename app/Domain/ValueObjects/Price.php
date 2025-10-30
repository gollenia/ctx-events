<?php

namespace Contexis\Events\Domain\ValueObjects;

class Price {
	public function __construct(
		public readonly int $amount_cents,
		public readonly string $currency
	) {}

	public function is_free(): bool {
		return $this->amount_cents === 0;
	}
}