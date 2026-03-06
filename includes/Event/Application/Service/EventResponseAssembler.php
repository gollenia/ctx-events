<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\Service;

use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Event\Application\DTOs\EventBookingSummary;
use Contexis\Events\Event\Application\DTOs\EventResponse;
use Contexis\Events\Event\Application\DTOs\EventResponseCollection;
use Contexis\Events\Event\Application\DTOs\EventIncludeRequest;
use Contexis\Events\Event\Domain\Enums\TicketScope;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventCollection;
use Contexis\Events\Event\Infrastructure\EventPost;
use Contexis\Events\Shared\Application\ValueObjects\UserContext;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Infrastructure\Wordpress\TaxonomyLoader;

final class EventResponseAssembler
{
	public function __construct(
		private EventLocations $locations,
		private EventImages $images,
		private EventPersons $persons,
		private EventTickets $tickets,
		private TaxonomyLoader $taxonomyLoader,
		private Clock $clock,
		private BookingRepository $bookingRepository,
	) {
	}

	public function mapEventCollection(EventCollection $events, EventIncludeRequest $includes, UserContext $userContext): EventResponseCollection {
		$locationCollectionDto = $includes->location ? $this->locations->preloadDtos($events) : null;
		$imageCollectionDto = $includes->image ? $this->images->preloadDtos($events) : null;
		$personCollectionDto = $includes->person ? $this->persons->preloadDtos($events) : null;
		$ticketBookingsMaps = $includes->bookings ? $this->bookingRepository->getTicketBookingsForEvents($events->getIds()) : null;

		$items = [];
		$now = $this->clock->now();

		foreach ($events as $event) {
            $locationDto = $locationCollectionDto?->findById($event->locationId ?? 0) ?: null;
            $personDto = $personCollectionDto?->findById($event->personId ?? 0) ?: null;
            $imageDto = $imageCollectionDto?->findById($event->imageId ?? 0) ?: null;
			
			if ($includes->categories) {
				$categories = $this->taxonomyLoader->termsForPost($event->id->toInt(), EventPost::CATEGORIES);
			}

			if ($includes->tags) {
				$tags = $this->taxonomyLoader->termsForPost($event->id->toInt(), EventPost::TAGS);
			}

			if ($includes->bookings) {
				$ticketBookingsMap = $ticketBookingsMaps[$event->id->toInt()] ?? null;
				$event = $event->withAvailabilitySnapshot($ticketBookingsMap);
				$bookingSummary = EventBookingSummary::fromEvent($event, $now, $userContext->isAnonymous());
			}
		
            $items[] = EventResponse::fromDomainModel(
                event: $event,
                locationDto: $locationDto,
                imageDto: $imageDto,
                personDto: $personDto,
				categories: $categories ?? null,
				tags: $tags ?? null,
				bookingSummary: $bookingSummary ?? null,
			);

			
        }

		return new EventResponseCollection(...$items);

	}
}