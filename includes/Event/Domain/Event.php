<?php

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Booking\Domain\BookingDecision;
use Contexis\Events\Booking\Domain\BookingPolicy;
use Contexis\Events\Location\Domain\LocationId;
use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Shared\Domain\ContentStatus;
use Contexis\Events\Shared\Domain\Traits\ReplicatesProperties;
use Contexis\Events\Shared\Domain\ValueObjects\AuthorId;
use DateTimeImmutable;

final class Event
{
    use ReplicatesProperties;

    public function __construct(
        public readonly EventId $id,
        public readonly ContentStatus $contentStatus,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $audience,
        public readonly EventStatus $eventStatus,
        public readonly DateTimeImmutable $startDate,
        public readonly DateTimeImmutable $endDate,
        public readonly DateTimeImmutable $createdAt,
        public readonly BookingPolicy $bookingPolicy,
        public readonly EventViewConfig $eventViewConfig,
        public readonly AuthorId $authorId,
        public readonly ?LocationId $locationId,
        public readonly ?ImageId $imageId,
        public readonly ?RecurrenceId $recurrenceId
    ) {
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

    public function isPublic()
    {
        return $this->eventStatus->isPublic();
    }

    public function isBookable(): BookingDecision
    {
        return $this->bookingPolicy->canBook();
    }

    public function withStatus(EventStatus $status): self
    {
        return $this->replicate([
            'eventStatus' => $status
        ]);
    }
}
