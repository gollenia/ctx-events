<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application\UseCases;

use Contexis\Events\Event\Application\DTOs\EventCriteria;
use Contexis\Events\Event\Application\DTOs\EventResponseCollection;
use Contexis\Events\Event\Application\DTOs\EventIncludeRequest;
use Contexis\Events\Event\Application\Service\EventResponseAssembler;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\EventStatusRepository;
use Contexis\Events\Shared\Application\ValueObjects\UserContext;

final class ListEvents
{
    public function __construct(
        private EventRepository $eventRepository,
		private EventStatusRepository $eventStatusRepository,
		private EventResponseAssembler $eventResponseAssembler,
		private UserContext $userContext
    ) {
    }

    public function execute(EventCriteria $criteria, EventIncludeRequest $includes): EventResponseCollection
    {
        $events = $this->eventRepository->search($criteria);
		$eventListDto = $this->eventResponseAssembler->mapEventCollection($events, $includes, $this->userContext)->withPagination($events->pagination());

		$statusCounts = $this->eventStatusRepository->getCountsByStatus();
		return $eventListDto->withStatusCounts($statusCounts);
    }
}
