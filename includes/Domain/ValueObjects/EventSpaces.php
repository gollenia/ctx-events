<?php

namespace Contexis\Events\Domain\ValueObjects;

class EventSpaces
{
    public function __construct(
        public readonly int $capacity,
        public readonly int $confirmed,
        public readonly int $pending,
        public readonly int $rejected,
        public readonly int $expired
    ) {
    }

    public function available(bool $holdPending = true): int
    {
        $holds = $holdPending ? $this->pending : 0;
        return max(0, $this->capacity - $this->confirmed - $holds);
    }

    public function is_sold_out(bool $holdPending = true): bool
    {
        return $this->available($holdPending) <= 0;
    }
}
