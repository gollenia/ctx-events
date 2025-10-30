<?php

namespace Contexis\Events\Application\DTO;

class LocationDto {
	public function __construct(
		public readonly int $id,
		public readonly string $title,
	) {}
}