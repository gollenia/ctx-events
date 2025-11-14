<?php

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Shared\Domain\ValueObjects\Price;
use DateTimeImmutable;

final class Ticket
{
    public function __construct(
        public readonly TicketId $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly Price $price,
        public readonly ?int $capacity,
        public readonly ?bool $enabled,
        public readonly ?DateTimeImmutable $salesStart,
        public readonly ?DateTimeImmutable $salesEnd,
    ) {
    }

    public function isFree(): bool
    {
        return $this->price->isFree();
    }

    public function isCurrentlyAvailable(DateTimeImmutable $now): bool
    {
        if ($this->enabled === false) {
            return false;
        }

        if ($this->salesStart !== null && $now < $this->salesStart) {
            return false;
        }
        if ($this->salesEnd !== null && $now > $this->salesEnd) {
            return false;
        }
        return true;
    }
}
