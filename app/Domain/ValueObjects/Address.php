<?php

namespace Contexis\Events\Domain\ValueObjects;

final class Address {
	public function __construct(
		public readonly ?string $street,
		public readonly ?string $city,
		public readonly ?string $region,
		public readonly ?string $postal_code,
		public readonly ?string $country_code
	) {}

	public function is_empty(): bool {
		return empty($this->street)
			&& empty($this->city)
			&& empty($this->region)
			&& empty($this->postal_code)
			&& empty($this->country_code);
	}
}	