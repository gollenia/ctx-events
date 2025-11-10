<?php

namespace Contexis\Events\Application\UseCases;

use Contexis\Events\Domain\Contracts\EventRepository;
use Contexis\Events\Domain\Contracts\EventCriteria;
use Contexis\Events\Domain\Models\Event;

final class GetEvents
{
	private EventRepository $eventRepository;

	public function __construct(EventRepository $eventRepository)
	{
		$this->eventRepository = $eventRepository;
	}

	/**
	 * @return Event[]
	 */
	public function execute(EventCriteria $criteria): array
	{
		return $this->eventRepository->query($criteria)->get();
	}
}