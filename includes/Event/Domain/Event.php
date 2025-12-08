<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Booking\Domain\BookingDecision;
use Contexis\Events\Booking\Domain\BookingPolicy;
use Contexis\Events\Location\Domain\LocationId;
use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Person\Domain\PersonId;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Domain\Traits\HasStatus;
use Contexis\Events\Shared\Domain\ValueObjects\AuthorId;
use DateTimeImmutable;

final class Event
{
    use HasStatus;

    public function __construct(
        public readonly EventId $id,
        private readonly Status $status,
        public readonly string $name,
        private readonly DateTimeImmutable $startDate,
        private readonly DateTimeImmutable $endDate,
        public readonly DateTimeImmutable $createdAt,
        public readonly BookingPolicy $bookingPolicy,
        public readonly EventViewConfig $eventViewConfig,
        public readonly AuthorId $authorId,
		public readonly ?string $description = null,
        public readonly ?string $audience = null,
        public readonly ?TicketCollection $tickets = null,
        public readonly ?LocationId $locationId = null,
        public readonly ?PersonId $personId = null,
        public readonly ?ImageId $imageId = null,
        public readonly ?RecurrenceId $recurrenceId = null
    ) {
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function start(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function end(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function duration(): \DateInterval
    {
        return $this->startDate->diff($this->endDate);
    }

    public function isOngoing(DateTimeImmutable $at): bool
    {
        return $at >= $this->startDate && $at <= $this->endDate;
    }

    public function isPast(DateTimeImmutable $at): bool
    {
        return $at >= $this->endDate;
    }

    public function meetsBookingPolicy(): BookingDecision
    {
        return $this->bookingPolicy->canBook();
    }

    public function bookingStartsAt(): ?DateTimeImmutable
    {
        return $this->bookingPolicy->start();
    }

    public function bookingEndsAt(): ?DateTimeImmutable
    {
        return $this->bookingPolicy->end();
    }
}
