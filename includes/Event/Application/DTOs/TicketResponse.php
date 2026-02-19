<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

use Contexis\Events\Event\Domain\Ticket;
use Contexis\Events\Shared\Application\Contracts\DTO;

final class TicketResponse implements DTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $priceInCents,
        public readonly string $currency,
        public readonly int $availableQuantity
    ) {
    }

    public static function fromDomainModel(Ticket $ticket): self
    {
        return new self(
            id: $ticket->id->toString(),
            name: $ticket->name,
            priceInCents: $ticket->price->amountCents,
            currency: $ticket->price->currency,
            availableQuantity: $ticket->capacity ?? 0
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price_in_cents' => $this->priceInCents,
            'currency' => $this->currency,
            'available_quantity' => $this->availableQuantity,
        ];
    }
}
