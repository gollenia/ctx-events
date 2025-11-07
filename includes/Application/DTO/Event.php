<?php

namespace Contexis\Events\Application\DTO;

use Contexis\Events\Domain\Collections\TicketCollection;
use Contexis\Events\Domain\Models\Location;
use Contexis\Events\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Domain\ValueObjects\EventSchedule;
use Contexis\Events\Domain\ValueObjects\EventStatus;
use Contexis\Events\Domain\ValueObjects\Media;
use Contexis\Events\Application\DTO as DTO;
use DateTime;
use DateTimeImmutable;

class Event
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $audience,
        public readonly EventStatus $eventStatus,
        public readonly DateTimeImmutable $startDate,
        public readonly DateTimeImmutable $endDate,
        public readonly BookingPolicy $bookingPolicy,
        public readonly ?DTO\EventIncludes $includes,
    ) {
    }

    public static function fromDomainModel(\Contexis\Events\Domain\Models\Event $event, ?DTO\EventIncludes $includes): self
    {
        return new self(
            id: $event->id->toInt(),
            name: $event->name,
            description: $event->description,
            audience: $event->audience,
            eventStatus: $event->eventStatus,
            startDate: $event->startDate,
            endDate: $event->endDate,
            bookingPolicy: $event->bookingPolicy,
            includes: $includes,
        );
    }
}
