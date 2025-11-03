<?php

namespace Contexis\Events\Domain\Models;

use Contexis\Events\Domain\Models\Form;
use Contexis\Events\Domain\ValueObjects\Price;
use DateTimeImmutable;

final class Ticket {
	public function __construct(
		public readonly string $id,
		public readonly string $name,
		public readonly ?string $description,
		public readonly Price $price,
		public readonly ?int $capacity,
		public readonly ?int $min_per_booking,
		public readonly ?int $max_per_booking,
		public readonly ?bool $enabled,
		public readonly ?DateTimeImmutable $sales_start,
		public readonly ?DateTimeImmutable $sales_end,

	) {}

	public function is_free(): bool {
		return $this->price->is_free();
	}

	public function is_available(): bool {
		if ($this->enabled === false) {
			return false;
		}

		$now = new DateTimeImmutable();
		if ($this->sales_start !== null && $now < $this->sales_start) {
			return false;
		}
		if ($this->sales_end !== null && $now > $this->sales_end) {
			return false;
		}
		return true;
	}
    
}