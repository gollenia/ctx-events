<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\ValueObjects;

final class Price
{
    public function __construct(
        public readonly int $amount_cents,
        public readonly string $currency
    ) {
        if ($amount_cents < 0) {
            throw new \InvalidArgumentException('Amount cents cannot be negative');
        }
    }

    public static function fromFloat(float $amount, string $currency = 'USD'): self
    {
        return new self((int) round($amount * 100), $currency);
    }

    public static function from(int $amount_cents, string $currency = 'USD'): self
    {
        return new self($amount_cents, $currency);
    }

    public function withAmount(int $amount_cents): self
    {
        return clone($this, ['amount_cents' => $amount_cents]);
    }

    public function minus(int $cents): self
    {
        return $this->withAmount(max(0, $this->amount_cents - $cents));
    }

    public function percentageOf(int $percent): int
    {
        return (int) ($this->amount_cents * $percent / 100);
    }

    public function isFree(): bool
    {
        return $this->amount_cents === 0;
    }

    public function equals(Price $other): bool
    {
        return $this->amount_cents === $other->amount_cents && $this->currency === $other->currency;
    }
}
