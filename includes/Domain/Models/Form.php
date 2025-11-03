<?php

namespace Contexis\Events\Domain\Models;

class Form {
	public function __construct(
		public readonly string $id,
		public readonly string $name,
		public readonly ?string $description,
		public readonly array $fields
	) {}
}