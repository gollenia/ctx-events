<?php

namespace Contexis\Events\Shared\Domain\ValueObjects;

final readonly class Currency
{
	private function __construct(
		private string $code,
	) {
	}

	public static function fromCode(string $code): self
	{
		$normalizedCode = strtoupper($code);
		if (!preg_match('/^[A-Z]{3}$/', $normalizedCode)) {
			throw new \InvalidArgumentException("Invalid ISO currency code: $code");
		}
		return new self($normalizedCode);
	}

	public function equals(Currency $other): bool
	{
		return $this->code === $other->code;
	}

	public function toString()
	{
		return $this->code;
	}
}