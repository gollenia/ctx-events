<?php

namespace Contexis\Events\Application\UseCases;

use Contexis\Events\Application\DTO\EventDto;
use Contexis\Events\Application\Security\ViewContext;
use Contexis\Events\Domain\Models\Event;
use Contexis\Events\Domain\Models\Location;
use Contexis\Events\Domain\Repositories\EventRepository;
use Contexis\Events\Domain\Repositories\SpeakerRepository;
use Contexis\Events\Domain\Repositories\ImageRepository;
use Contexis\Events\Domain\Repositories\LocationRepository;
use Contexis\Events\Domain\Repositories\PersonRepository;
use Contexis\Events\Application\Query\EventIncludes;
use Contexis\Events\Application\DTO as DTO;
use Contexis\Events\Domain\ValueObjects\Id\EventId;

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

    public function execute(int $id, EventIncludes $includes, ViewContext $viewContext): DTO\Event|null
    {
        $event = $this->eventRepository->find(EventId::from($id));

        if (!$event) {
            return null;
        }

        $location = $includes->location ? $this->locationRepository->find($event->locationId) : null;
        $image = $includes->image ? $this->imageRepository->find($event->imageId) : null;

        $locationDto = $location ? DTO\Location::fromDomainModel($location) : null;

        $imageDto = $image ? DTO\Image::fromDomainModel($image) : null;

        $response = DTO\Event::fromDomainModel(
            $event,
            new DTO\EventIncludes(
                location: $locationDto,
                image: $imageDto
            )
        );

        return $response;
    }
}
