<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\UseCases;

use Contexis\Events\Booking\Application\Services\CancelBookingsForEvent;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\EventStatusRepository;
use Contexis\Events\Event\Domain\Enums\EventStatus;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Application\Contracts\UseCase;

class CancelEvent implements UseCase
{
	public function __construct(
		private EventRepository $eventRepository,
		private EventStatusRepository $eventStatusRepository,
		private CancelBookingsForEvent $cancelBookingsForEvent
	) {
	}

	public function execute(int $eventId, bool $notifyCustomers, string $attendeeMessage): bool
	{
		$event = $this->eventRepository->find(EventId::from($eventId));

		if ($event === null) {
			return false;
		}

		$cancelledEvent = $event->setStatus(EventStatus::Cancelled);
		$this->eventStatusRepository->saveStatus($cancelledEvent);
		$this->cancelBookingsForEvent->execute($cancelledEvent->id, $notifyCustomers, $attendeeMessage);

		return true;
	}
}
