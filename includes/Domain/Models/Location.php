<?php

namespace Contexis\Events\Domain\Models;

use Contexis\Events\Domain\ValueObjects\Address;
use Contexis\Events\Domain\ValueObjects\GeoPosition;

final class Location {
	public function __construct(
		public readonly string $id,
		public readonly string $name,
		public readonly ?Address $address,
		public readonly ?GeoPosition $geo_position,
		public readonly ?string $url
	) {}
}