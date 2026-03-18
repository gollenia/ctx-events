<?php

namespace Contexis\Events\Event\Application\UseCases;

use Contexis\Events\Booking\Application\Services\CancelBookingsForEvent;
use Contexis\Events\Event\Application\DTOs\EventResponse;
use Contexis\Events\Event\Domain\Enums\EventStatus;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\EventStatusRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Application\Contracts\UseCase;
use Contexis\Events\Shared\Domain\ValueObjects\Status;

class CancelEvent implements UseCase
{
	public function __construct(
		private EventRepository $eventRepository,
		private EventStatusRepository $eventStatusRepository,
		private CancelBookingsForEvent $cancelBookingsForEvent
	) {
	}

	public function execute(int $eventId): ?EventResponse
	{
		$event = $this->eventRepository->find(EventId::from($eventId));
		$event->setStatus(EventStatus::Trash);
		$this->eventStatusRepository->saveStatus($event);
		$this->cancelBookingsForEvent->execute($event->id);
		return null;
	}
}
