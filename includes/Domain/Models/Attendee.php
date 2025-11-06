<?php

namespace Contexis\Events\Domain\Models;

use Contexis\Events\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Domain\ValueObjects\Id\BookingId;
use Contexis\Events\Domain\ValueObjects\Id\TicketId;

final class Attendee
{
    public function __construct(
        public readonly TicketId $ticket_id,
        public readonly BookingId $booking_id,
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $email,
        public readonly array $metadata = []
    ) {
    }
}
