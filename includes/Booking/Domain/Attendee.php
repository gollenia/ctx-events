<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

use Contexis\Events\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Domain\ValueObjects\Id\AttendeeId;
use Contexis\Events\Domain\ValueObjects\Id\BookingId;
use Contexis\Events\Domain\ValueObjects\Id\TicketId;
use DateTimeImmutable;

final class Attendee
{
    public function __construct(
        public readonly AttendeeId $id,
        public readonly TicketId $ticketId,
        public readonly BookingId $bookingId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?DateTimeImmutable $birthDate = null,
        public readonly array $metadata = []
    ) {
    }

    public function ageOn(DateTimeImmutable $reference): ?int
    {
        if (!$this->birthDate) {
            return null;
        }

        return $this->birthDate->diff($reference)->y;
    }
}
