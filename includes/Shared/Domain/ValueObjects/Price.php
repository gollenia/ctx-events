<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\ValueObjects;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Price')]
final class Price
{
    public function __construct(
        public readonly int $amountCents,
        public readonly string $currency
    ) {
        if ($amountCents < 0) {
            throw new \InvalidArgumentException('Amount cents cannot be negative');
        }
    }

    public static function fromFloat(float $amount, string $currency): self
    {
        return new self((int) round($amount * 100), $currency);
    }

    public static function from(int $amountCents, string $currency): self
    {
        return new self($amountCents, $currency);
    }

    public function withAmount(int $amountCents): self
    {
        return clone($this, ['amountCents' => $amountCents]);
    }

    public function minus(int $cents): self
    {
        return $this->withAmount(max(0, $this->amountCents - $cents));
    }

    public function percentageOf(int $percent): int
    {
        return (int) ($this->amountCents * $percent / 100);
    }

    public function isFree(): bool
    {
        return $this->amountCents === 0;
    }

    public function toFloat(): float
    {
        return $this->amountCents / 100;
    }

	public function toInt(): int
	{
		return $this->amountCents;
	}

    public function equals(Price $other): bool
    {
        return $this->amountCents === $other->amountCents && $this->currency === $other->currency;
    }

	public function toArray(): array
	{
		return [
			'amountCents' => $this->amountCents,
			'currency' => $this->currency
		];
	}
}
