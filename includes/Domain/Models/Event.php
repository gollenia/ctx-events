<?php

namespace Contexis\Events\Domain\Models;

use Contexis\Events\Domain\ValueObjects\BookingDecision;
use Contexis\Events\Domain\ValueObjects\Id\ImageId;
use Contexis\Events\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Domain\ValueObjects\EventViewConfig;
use Contexis\Events\Domain\ValueObjects\Id\EventId;
use Contexis\Events\Domain\ValueObjects\EventStatus;
use Contexis\Events\Domain\ValueObjects\Id\AuthorId;
use Contexis\Events\Domain\ValueObjects\Id\LocationId;
use Contexis\Events\Domain\ValueObjects\Id\RecurrenceId;
use DateTimeImmutable;

final class Event
{
    use \Contexis\Events\Core\Traits\ReplicatesProperties;

    public function __construct(
        public readonly EventId $id,
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
