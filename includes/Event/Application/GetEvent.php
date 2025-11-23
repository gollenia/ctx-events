<?php

namespace Contexis\Events\Event\Application;

use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\EventId;
use Contexis\Events\Location\Application\LocationDto;
use Contexis\Events\Location\Domain\LocationRepository;
use Contexis\Events\Media\Application\ImageDto;
use Contexis\Events\Media\Domain\ImageRepository;
use Contexis\Events\Person\Application\PersonDto;
use Contexis\Events\Person\Domain\PersonRepository;
use Contexis\Events\Shared\Domain\ValueObjects\ViewContext;

class GetEvent
{
    public function __construct(
        private EventRepository $eventRepository,
        private PersonRepository $personRepository,
        private ImageRepository $imageRepository,
        private LocationRepository $locationRepository,
        private EventPolicy $eventPolicy
    ) {
        $this->eventRepository = $eventRepository;
        $this->personRepository = $personRepository;
        $this->imageRepository = $imageRepository;
        $this->locationRepository = $locationRepository;
        $this->eventPolicy = $eventPolicy;
    }

    public function execute(int $id, EventIncludes $includes, ViewContext $viewContext): EventDto|null
    {

        $event = $this->eventRepository->find(EventId::from($id));

        if (!$event) {
            return null;
        }

        if (!$this->eventPolicy->canView($event, $viewContext)) {
            return null;
        }

        $location = $includes->location ? $this->locationRepository->find($event->locationId) : null;
        $person = $includes->person ? $this->personRepository->find($event->personId) : null;
        $image = $includes->image ? $this->imageRepository->find($event->imageId) : null;

        $locationDto = $location ? LocationDto::fromDomainModel($location) : null;
        $personDto = $person ? PersonDto::fromDomainModel($person) : null;
        $imageDto = $image ? ImageDto::fromDomainModel($image) : null;

        $response = EventDto::fromDomainModel(
            event: $event,
            locationDto: $locationDto,
            imageDto: $imageDto,
            personDto: $personDto
        );

        return $response;
    }
}
