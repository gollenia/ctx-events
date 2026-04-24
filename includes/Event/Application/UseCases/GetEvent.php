<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application\UseCases;

use Contexis\Events\Event\Application\DTOs\EventResponse;
use Contexis\Events\Event\Application\DTOs\EventIncludeRequest;
use Contexis\Events\Event\Application\Service\EventPolicy;
use Contexis\Events\Event\Application\Service\EventTickets;
use Contexis\Events\Event\Domain\Enums\TicketScope;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Infrastructure\EventPost;
use Contexis\Events\Event\Infrastructure\EventTaxonomy;
use Contexis\Events\Form\Domain\FormRepository;
use Contexis\Events\Location\Application\LocationDto;
use Contexis\Events\Location\Domain\LocationRepository;
use Contexis\Events\Media\Application\ImageDto;
use Contexis\Events\Media\Domain\ImageRepository;
use Contexis\Events\Person\Application\PersonDto;
use Contexis\Events\Person\Domain\PersonRepository;
use Contexis\Events\Shared\Application\ValueObjects\UserContext;
use Contexis\Events\Shared\Infrastructure\Wordpress\TaxonomyLoader;


final class GetEvent
{
	public function __construct(
		private EventRepository $eventRepository,
		private PersonRepository $personRepository,
		private ImageRepository $imageRepository,
		private LocationRepository $locationRepository,
		private EventPolicy $eventPolicy,
		private TaxonomyLoader $taxonomyLoader
	) {
	}

	public function execute(int $id, EventIncludeRequest $includes, UserContext $userContext): ?EventResponse
	{

		$event = $this->eventRepository->find(EventId::from($id));

		if (!$event) {
			return null;
		}

		if (!$this->eventPolicy->userCanView($event, $userContext)) {
			return null;
		}

		$location = $includes->location ? $this->locationRepository->find($event->locationId) : null;
		$person = $includes->person ? $this->personRepository->find($event->personId) : null;
		$image = $includes->image ? $this->imageRepository->find($event->imageId) : null;
		$categories = $includes->categories ? $this->taxonomyLoader->termsForPost($event->id->toInt(), EventTaxonomy::CATEGORIES) : null;
		$tags = $includes->tags ? $this->taxonomyLoader->termsForPost($event->id->toInt(), EventTaxonomy::TAGS) : null;
		// Missing: Available Coupons -really?
		// Missing: Booking Info
		

		$response = EventResponse::fromDomainModel(
			event: $event,
			locationDto: $location ? LocationDto::fromDomainModel($location) : null,
			imageDto: $image ? ImageDto::fromDomainModel($image) : null,
			personDto: $person ? PersonDto::fromDomainModel($person) : null,
			categories: $categories,
			tags: $tags
		);

		
		return $response;
	}
}
