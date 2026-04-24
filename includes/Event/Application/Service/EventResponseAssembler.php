<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\Service;

use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\ValueObjects\TicketBookingsMap;
use Contexis\Events\Event\Application\DTOs\EventBookingSummary;
use Contexis\Events\Event\Application\DTOs\EventResponse;
use Contexis\Events\Event\Application\DTOs\EventResponseCollection;
use Contexis\Events\Event\Application\DTOs\EventIncludeRequest;
use Contexis\Events\Event\Domain\Enums\TicketScope;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventCollection;
use Contexis\Events\Event\Infrastructure\EventPost;
use Contexis\Events\Event\Infrastructure\EventTaxonomy;
use Contexis\Events\Shared\Application\ValueObjects\UserContext;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Infrastructure\Wordpress\TaxonomyLoader;

final class EventResponseAssembler
{
	public function __construct(
		private EventLocations $locations,
		private EventImages $images,
		private EventPersons $persons,
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
			$locationDto = $event->locationId !== null
				? $locationCollectionDto?->findById($event->locationId)
				: null;
			$personDto = $event->personId !== null
				? $personCollectionDto?->findById($event->personId->toInt())
				: null;
			$imageDto = $event->imageId !== null
				? $imageCollectionDto?->findById($event->imageId)
				: null;
			$categories = null;
			$tags = null;
			$bookingSummary = null;

			if ($includes->categories) {
				$categories = $this->taxonomyLoader->termsForPost($event->id->toInt(), EventTaxonomy::CATEGORIES);
			}

			if ($includes->tags) {
				$tags = $this->taxonomyLoader->termsForPost($event->id->toInt(), EventTaxonomy::TAGS);
			}

			if ($includes->bookings) {
				$ticketBookingsMap = $ticketBookingsMaps[$event->id->toInt()] ?? TicketBookingsMap::empty();
				$bookingSummary = EventBookingSummary::fromEvent($event, $now, $userContext->isAnonymous(), $ticketBookingsMap);
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

		return EventResponseCollection::from(...$items);
	}
}
