<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

class EventSpaces
{
    public function __construct(
        public readonly int $capacity,
        public readonly int $confirmed,
        public readonly int $pending,
        public readonly int $waiting,
        public readonly int $rejected,
        public readonly int $expired
    ) {
    }

    public function available(): int
    {
        return max(0, $this->capacity - $this->confirmed - $this->pending);
    }

    public function isSoldOut(): bool
    {
        return $this->available() <= 0;
    }

    public function hasSpaces(): bool
    {
        return $this->capacity > 0;
    }

    public function isOverbooked(): bool
    {
        return $this->confirmed + $this->pending > $this->capacity;
    }
}
