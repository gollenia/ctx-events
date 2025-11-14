<?php

namespace Contexis\Events\Event\Application;

use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\EventId;
use Contexis\Events\Location\Application\LocationDto;
use Contexis\Events\Location\Domain\LocationRepository;
use Contexis\Events\Media\Application\ImageDto;
use Contexis\Events\Media\Domain\ImageRepository;
use Contexis\Events\Person\Domain\PersonRepository;
use Contexis\Events\Shared\Domain\ValueObjects\ViewContext;

class GetEvent
{
    private EventRepository $eventRepository;
    private PersonRepository $personRepository;
    private ImageRepository $imageRepository;
    private LocationRepository $locationRepository;

    public function __construct(EventRepository $eventRepository, PersonRepository $personRepository, ImageRepository $imageRepository, LocationRepository $locationRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->personRepository = $personRepository;
        $this->imageRepository = $imageRepository;
        $this->locationRepository = $locationRepository;
    }

    public function execute(int $id, EventIncludes $includes, ViewContext $viewContext): EventDto|null
    {
        $event = $this->eventRepository->find(EventId::from($id));

        if (!$event) {
            return null;
        }

        $location = $includes->location ? $this->locationRepository->find($event->locationId) : null;
        $image = $includes->image ? $this->imageRepository->find($event->imageId) : null;

        $locationDto = $location ? LocationDto::fromDomainModel($location) : null;

        $imageDto = $image ? ImageDto::fromDomainModel($image) : null;

        $response = EventDto::fromDomainModel(
            event: $event,
            locationDto: $locationDto,
            imageDto: $imageDto
        );

        return $response;
    }
}
