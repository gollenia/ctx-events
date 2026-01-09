<?php

namespace Contexis\Events\Event\Application\Subscribers;

use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\Signals\EventCapacityChanged;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSpaceSubscriber implements EventSubscriberInterface
{
    public function __construct(
        public readonly EventRepository $eventRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EventCapacityChanged::class => 'onEventCapacityChanged',
        ];
    }

    public function onEventCapacityChanged(EventCapacityChanged $signal): void
    {
        $event = $this->eventRepository->get($signal->eventId);
		error_log('Event capacity changed. Event: ' . $signal->eventId->toInt());
    }
}