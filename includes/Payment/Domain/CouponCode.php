<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

class CouponCode
{
    public function __construct(
        public readonly string $value
    ) {
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $other::class === static::class && $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
