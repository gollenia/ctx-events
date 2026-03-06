<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

use Contexis\Events\Booking\Domain\ValueObjects\AttendeeId;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use DateTimeImmutable;

final class Attendee
{
    public function __construct(
        public readonly TicketId $ticketId,
		public readonly Price $ticketPrice,
        public readonly ?string $firstName,
        public readonly ?string $lastName,
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
