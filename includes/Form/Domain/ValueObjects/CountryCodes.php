<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

final class CountryCodes
{

	/** @var string[] */
	public readonly array $codes;

	public function __construct(
		array $codes
	) {
		$upper = array_map(strtoupper(...), $codes);
        $unique = array_unique($upper);
        $this->codes = array_values($unique);
	}

	public static function of(string ...$codes): self
	{
		return new self($codes);
	}

	public function toArray(): array
	{
		return $this->codes;
	}

	public function contains(string $code): bool
	{
		if ($this->codes !== []) {
			return in_array(strtoupper($code), $this->codes, true);
		}
		
		return true;
	}

	public function count(): int
	{
		return count($this->codes);
	}
}
