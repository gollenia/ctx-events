<?php

namespace Contexis\Events\Shared\Domain\ValueObjects;

final readonly class PriceRange
{
	public function __construct(
		public ?Price $min,
		public ?Price $max,
	) {
		if ($min->currency !== $max->currency) {
            throw new \DomainException("PriceRange min and max must have the same currency");
        }
	}

	public static function fromPrices(Price ...$prices): self
	{
		if (empty($prices)) {
			return self::empty();
		}

		$min = $prices[0];
		$max = $prices[0];

		foreach ($prices as $price) {
			if ($price->amountCents < $min->amountCents) {
				$min = $price;
			}
			if ($price->amountCents > $max->amountCents) {
				$max = $price;
			}
		}

		return new self($min, $max);
	}

	public static function empty(): self
	{
		return new self(null, null);
	}

	public function isEmpty(): bool
    {
        return $this->min === null;
    }
}