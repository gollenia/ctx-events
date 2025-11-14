<?php

namespace Contexis\Events\Event\Domain;

final class TicketId
{
    public function __construct(private readonly string $id)
    {
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function equals(TicketId $other): bool
    {
        return $this->id === $other->id;
    }
}
