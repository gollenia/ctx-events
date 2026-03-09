<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use DateTimeImmutable;

final readonly class Attendee
{
    public function __construct(
        public TicketId $ticketId,
		public Price $ticketPrice,
		public ?PersonName $name,
        public ?DateTimeImmutable $birthDate = null,
        public array $metadata = []
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
