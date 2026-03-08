<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\DTOs;

final readonly class BookingListItem
{
    public function __construct(
        public int $id,
        public string $reference,
        public string $email,
        public string $firstName,
        public string $lastName,
        public int $eventId,
        public string $eventTitle,
        public int $status,
        public int $finalPrice,
        public int $donationAmount,
        public ?string $gateway,
        public \DateTimeImmutable $bookingTime,
        public array $ticketBreakdown = [],   // [ticketId => count]
        public int $spaces = 0,
        public ?string $gatewayName = null,
    ) {
    }

    public function withTicketBreakdown(array $ticketBreakdown): self
    {
        return clone($this, [
            'ticketBreakdown' => $ticketBreakdown,
            'spaces' => array_sum($ticketBreakdown),
        ]);
    }

    public function withGatewayName(string $gatewayName): self
    {
        return clone($this, ['gatewayName' => $gatewayName]);
    }
}
