<?php

namespace Contexis\Events\Shared\Domain\ValueObjects;

class Price
{
    public function __construct(
        public readonly int $amount_cents,
        public readonly string $currency
    ) {
        if ($amount_cents < 0) {
            throw new \InvalidArgumentException('Amount cents cannot be negative');
        }
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
