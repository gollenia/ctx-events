<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\Abstract;

abstract class Id implements \Stringable
{
    final public function __construct(
        private readonly int $value
    ) {
    }

    public static function from(?int $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return new static($value);
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $other::class === static::class && $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
