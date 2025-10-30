<?php

namespace Contexis\Events\Application\UseCases;

use Contexis\Events\Application\DTO\EventDto;
use Contexis\Events\Domain\Repositories\EventRepository;

class GetEvent {
    private EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository) {
        $this->eventRepository = $eventRepository;
    }

    public function execute(int $id): EventDto|null {
        $event = $this->eventRepository->by_id($id);

		if (!$event) {
			return null;
		}

		return new EventDto(
			id: $event->id,
			title: $event->title,
			author: $event->author,
			status: $event->status,
			description: $event->description,
			schedule: $event->schedule,
			booking_policy: $event->booking_policy,
			tickets: $event->tickets
		
		);
	}
}