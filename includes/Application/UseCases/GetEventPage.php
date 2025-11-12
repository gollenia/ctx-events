<?php

namespace Contexis\Events\Application\UseCases;

use Contexis\Events\Application\Query\ListEventsQuery;
use Contexis\Events\Domain\Contracts\EventRepository;
use Contexis\Events\Domain\Contracts\EventCriteria;
use Contexis\Events\Domain\Models\Event;

final class GetEventPage
{
	private EventRepository $eventRepository;

	public function __construct(EventRepository $eventRepository)
	{
		$this->eventRepository = $eventRepository;
	}

	/**
	 * @return Event[]
	 */
	public function execute(ListEventsQuery $query): array
	{
		return $this->eventRepository->query($query)->get();
	}
}