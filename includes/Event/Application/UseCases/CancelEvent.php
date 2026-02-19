<?php

namespace Contexis\Events\Event\Application\UseCases;

use Contexis\Events\Event\Application\DTOs\EventResponse;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Application\Contracts\UseCase;
use Contexis\Events\Shared\Domain\ValueObjects\Status;

class CancelEvent implements UseCase
{
	public function __construct(
		private EventRepository $eventRepository,	) {
	}

	/**
	 * @param CancelEventRequest $request
	 * @return EventResponse|null
	 */
	public function execute(int $eventId): ?EventResponse
	{
		$event = $this->eventRepository->find(EventId::from($eventId));
		$event->setStatus(Status::Trash);
		$this->eventRepository->saveStatus($event);
		return null;
	}
}
