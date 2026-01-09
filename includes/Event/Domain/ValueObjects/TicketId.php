<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain\ValueObjects;

final class TicketId
{
    public function __construct(
        private readonly string $value
    ) {
    }

    public static function from(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return new static($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $other::class === static::class && $this->value === $other->value;
    }
}

