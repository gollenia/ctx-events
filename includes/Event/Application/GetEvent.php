<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application;

use Contexis\Events\Event\Application\Service\EventTickets;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\EventId;
use Contexis\Events\Event\Infrastructure\EventPost;
use Contexis\Events\Location\Application\LocationDto;
use Contexis\Events\Location\Domain\LocationRepository;
use Contexis\Events\Media\Application\ImageDto;
use Contexis\Events\Media\Domain\ImageRepository;
use Contexis\Events\Person\Application\PersonDto;
use Contexis\Events\Person\Domain\PersonRepository;
use Contexis\Events\Shared\Application\ValueObjects\UserContext;
use Contexis\Events\Shared\Infrastructure\Wordpress\TaxonomyLoader;

class GetEvent
{
    public function __construct(
        private EventRepository $eventRepository,
        private PersonRepository $personRepository,
        private ImageRepository $imageRepository,
        private LocationRepository $locationRepository,
        private EventPolicy $eventPolicy,
        private TaxonomyLoader $taxonomyLoader,
    ) {
    }

    public function execute(int $id, EventIncludes $includes, UserContext $userContext): EventDto|null
    {

        $event = $this->eventRepository->find(EventId::from($id));

        if (!$event) {
            return null;
        }

        if (!$this->eventPolicy->userCanView($event, $userContext)) {
            return null;
        }

        $location = $includes->hasLocation() ? $this->locationRepository->find($event->locationId) : null;
        $person = $includes->hasPerson() ? $this->personRepository->find($event->personId) : null;
        $image = $includes->hasImage() ? $this->imageRepository->find($event->imageId) : null;
        $categories = $includes->hasCategories() ? $this->taxonomyLoader->termsForPost($event->id->toInt(), EventPost::CATEGORIES) : null;
        $tags = $includes->hasTags() ? $this->taxonomyLoader->termsForPost($event->id->toInt(), EventPost::TAGS) : null;
        $tickets = $includes->hasTickets() ? EventTickets::onlyBookable()->getAllowedTickets($event) : null;
        // Missing: Forms
        // Missing: Available Coupons
        // Missing: Booking Info

        $response = EventDto::fromDomainModel(
            event: $event,
            locationDto: $location ? LocationDto::fromDomainModel($location) : null,
            imageDto: $image ? ImageDto::fromDomainModel($image) : null,
            personDto: $person ? PersonDto::fromDomainModel($person) : null,
            categories: $categories,
            ticketsDto: $tickets,
            tags: $tags
        );

        return $response;
    }
}
