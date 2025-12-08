<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application;

use Contexis\Events\Event\Application\Service\EventImages;
use Contexis\Events\Event\Application\Service\EventLocations;
use Contexis\Events\Event\Application\Service\EventLocationService;
use Contexis\Events\Event\Application\Service\EventPersons;
use Contexis\Events\Event\Application\Service\EventTickets;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Infrastructure\EventPost;
use Contexis\Events\Location\Domain\LocationRepository;
use Contexis\Events\Media\Application\ImageDto;
use Contexis\Events\Person\Domain\PersonRepository;
use Contexis\Events\Media\Domain\ImageRepository;
use Contexis\Events\Shared\Infrastructure\Wordpress\TaxonomyLoader;

final class ListEvents
{
    public function __construct(
        private EventRepository $eventRepository,
        private PersonRepository $personRepository,
        private LocationRepository $locationRepository,
        private ImageRepository $imageRepository,
		private TaxonomyLoader $taxonomyLoader
    ) {
    }

    public function execute(EventCriteria $criteria): EventDtoCollection
    {
        $events = $this->eventRepository->search($criteria);

        $locationCollectionDto = $criteria->includes->hasLocation() ? (EventLocations::create($this->locationRepository))->preloadDtos($events) : null;
        $personCollectionDto = $criteria->includes->hasPerson() ? (EventPersons::create($this->personRepository))->preloadDtos($events) : null;
        $imageCollectionDto = $criteria->includes->hasImage() ? (EventImages::create($this->imageRepository))->preloadDtos($events) : null;
        $ticketService = EventTickets::create($criteria->includes->allTickets());
        $items = [];

        foreach ($events as $event) {
            $locationDto = $locationCollectionDto?->findById($event->locationId ?? 0) ?: null;
            $personDto = $personCollectionDto?->findById($event->personId ?? 0) ?: null;
            $imageDto = $imageCollectionDto?->findById($event->imageId ?? 0) ?: null;
            $tickets = $ticketService->getAllowedTickets($event);

			if ($criteria->includes->hasCategories()) {
				$categories = $this->taxonomyLoader->termsForPost($event->id->toInt(), EventPost::CATEGORIES);
			}

			if ($criteria->includes->hasTags()) {
				$tags = $this->taxonomyLoader->termsForPost($event->id->toInt(), EventPost::TAGS);
			}
		
            $items[] = EventDto::fromDomainModel(
                event: $event,
                locationDto: $locationDto,
                imageDto: $imageDto,
                personDto: $personDto,
                ticketsDto: $tickets,
				categories: $categories ?? null,
				tags: $tags ?? null
            );
        }

        $eventListDto = new EventDtoCollection(...$items)->withPagination($events->pagination());

		
        return $eventListDto;
    }
}
