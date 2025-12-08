<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application;

use Contexis\Events\Event\Domain\Ticket;
use Contexis\Events\Shared\Application\Contracts\DTO;

final class TicketDto implements DTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $priceInCents,
        public readonly string $currency,
        public readonly int $availableQuantity
    ) {
    }

    public static function fromDomain(Ticket $ticket): self
    {
        return new self(
            id: $ticket->id->toInt(),
            name: $ticket->name,
            priceInCents: $ticket->price->amount_cents,
            currency: $ticket->price->currency,
            availableQuantity: $ticket->capacity ?? 0
        );
    }
}
