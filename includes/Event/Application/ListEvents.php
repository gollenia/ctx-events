<?php

namespace Contexis\Events\Event\Application;

use Contexis\Events\Event\Domain\EventRepository;

final class ListEvents
{
    private EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @return Event[]
     */
    public function execute(EventCriteria $criteria): EventListDto
    {

        $events = $this->eventRepository->search($criteria);
        return EventListDto::fromCollection($events);
    }
}
