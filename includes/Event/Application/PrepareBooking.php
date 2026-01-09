<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application;

use Contexis\Events\Event\Application\Service\EventTickets;
use Contexis\Events\Event\Domain\EventRepository;
	use Contexis\Events\Event\Domain\ValueObjects\EventId;
	use Contexis\Events\Event\Infrastructure\EventPost;
	use Contexis\Events\Form\Domain\FormRepository;
	use Contexis\Events\Location\Application\LocationDto;
	use Contexis\Events\Location\Domain\LocationRepository;
	use Contexis\Events\Media\Application\ImageDto;
	use Contexis\Events\Media\Domain\ImageRepository;
	use Contexis\Events\Person\Application\PersonDto;
	use Contexis\Events\Person\Domain\PersonRepository;
    use Contexis\Events\Platform\Demo\HelloWorldEvent;
    use Contexis\Events\Shared\Application\ValueObjects\UserContext;
	use Contexis\Events\Shared\Infrastructure\Wordpress\TaxonomyLoader;
	use Psr\EventDispatcher\EventDispatcherInterface;

	class GetEvent
	{
		public function __construct(
			private EventRepository $eventRepository,
			private PersonRepository $personRepository,
			private ImageRepository $imageRepository,
			private LocationRepository $locationRepository,
			private EventPolicy $eventPolicy,
			private TaxonomyLoader $taxonomyLoader,
			private FormRepository $formRepository,
			private EventDispatcherInterface $dispatcher
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

			$tickets = $includes->hasTickets() ? EventTickets::onlyBookable()->getAllowedTickets($event) : null;
			$bookingForm = $this->formRepository->find($event->forms->bookingForm);
			$attendeeForm = $this->formRepository->find($event->forms->attendeeForm);
			

			$response = EventDto::fromDomainModel(
				event: $event,
				ticketsDto: $tickets,
				bookingForm: $bookingForm,
				attendeeForm: $attendeeForm
			);

			
			return $response;
		}
	}
