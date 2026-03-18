<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

use Contexis\Events\Event\Domain\Ticket;
use Contexis\Events\Shared\Application\Contracts\DTO;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Ticket')]
final class TicketResponse implements DTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly Price $price,
        public readonly int $availableQuantity,
        public readonly ?int $ticketLimitPerBooking = null,
        public readonly ?int $remainingTickets = null,
        public readonly ?int $remainingOverallCapacity = null,
        public readonly ?int $bookingLimit = null,
    ) {
    }

    public static function fromDomainModel(
        Ticket $ticket,
        ?int $remainingTickets = null,
        ?int $ticketLimitPerBooking = null,
        ?int $remainingOverallCapacity = null,
        ?int $bookingLimit = null
    ): self
    {
        $normalizedTicketLimit = self::normalizeLimit($ticketLimitPerBooking ?? $ticket->max);

        return new self(
            id: $ticket->id->toString(),
            name: $ticket->name,
            price: $ticket->price,
            availableQuantity: $remainingTickets ?? $ticket->capacity ?? 0,
            ticketLimitPerBooking: $normalizedTicketLimit,
            remainingTickets: $remainingTickets,
            remainingOverallCapacity: $remainingOverallCapacity,
            bookingLimit: $bookingLimit,
        );
    }

    private static function normalizeLimit(?int $limit): ?int
    {
        if ($limit === null || $limit <= 0) {
            return null;
        }

        return $limit;
    }

	/**
	 * @return array<string, mixed>
	 */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
			'price' => $this->price,
            'available_quantity' => $this->availableQuantity,
            'ticket_limit_per_booking' => $this->ticketLimitPerBooking,
            'remaining_tickets' => $this->remainingTickets,
            'remaining_overall_capacity' => $this->remainingOverallCapacity,
            'booking_limit' => $this->bookingLimit,
        ];
    }
}
