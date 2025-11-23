<?php

namespace Contexis\Events\Event\Application;

use Contexis\Events\Booking\Domain\BookingPolicy;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventStatus;
use Contexis\Events\Location\Application\LocationDto;
use Contexis\Events\Location\Domain\Location;
use Contexis\Events\Media\Application\ImageDto;
use Contexis\Events\Media\Domain\Image;
use Contexis\Events\Person\Domain\PersonCollection;
use Contexis\Events\Person\Application\PersonDto;
use Contexis\Events\Shared\Application\Contracts\DTO;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use DateTimeImmutable;

class EventDto implements DTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $audience,
        public readonly Status $status,
        public readonly DateTimeImmutable $startDate,
        public readonly DateTimeImmutable $endDate,
        public readonly BookingPolicy $bookingPolicy,
        public readonly ?LocationDto $locationDto = null,
        public readonly ?ImageDto $imageDto = null,
        public readonly ?PersonDto $personDto = null
    ) {
    }

    public static function fromDomainModel(
        Event $event,
        ?LocationDto $locationDto = null,
        ?ImageDto $imageDto = null,
        ?PersonDto $personDto = null
    ): self {
        return new self(
            id: $event->id->toInt(),
            name: $event->name,
            description: $event->description,
            audience: $event->audience,
            status: $event->status,
            startDate: $event->startDate,
            endDate: $event->endDate,
            bookingPolicy: $event->bookingPolicy,
            locationDto: $locationDto,
            imageDto: $imageDto,
            personDto: $personDto
        );
    }
}
