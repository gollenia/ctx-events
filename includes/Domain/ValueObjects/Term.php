<?php

namespace Contexis\Events\Domain\ValueObjects;

final class Term {
	public function __construct(
		public readonly int $id,
		public readonly string $name,
		public readonly string $slug
	) {}

	public function is(string $slug): bool {
		return $this->slug === $slug;
	}
}