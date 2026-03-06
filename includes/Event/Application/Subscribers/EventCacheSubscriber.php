<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application\Subscribers;

use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Event\Application\DTOs\EventCacheSnapshot;
use Contexis\Events\Event\Domain\EventCacheRepository;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\Signals\EventAvailabilityChanged;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventCacheSubscriber implements EventSubscriberInterface
{
    public function __construct(
        public readonly EventRepository $eventRepository,
		public readonly BookingRepository $bookingRepository,
        public readonly EventCacheRepository $eventCacheRepository,
        public readonly Clock $clock,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EventAvailabilityChanged::class => 'onEventAvailabilityChanged',
        ];
    }

    public function onEventAvailabilityChanged(EventAvailabilityChanged $signal): void
    {
        $event = $this->eventRepository->find($signal->eventId);
		if ($event === null) {
			return;
		}

		$ticketIds = $event->tickets?->getTicketIds() ?? [];
		$bookingStats = $this->bookingRepository->getTicketBookingsForEvent($signal->eventId, $ticketIds);
        $now = $this->clock->now();
        $availableTickets = $event->tickets
            ?->getEnabledTickets()
            ->getValidTicketsForDate($now)
            ->getAvailableTickets($bookingStats);

        $priceRange = $availableTickets?->getPriceRange($now);

		
		$this->eventCacheRepository->saveCache(new EventCacheSnapshot(
            eventId: $event->id->toInt(),
            minPriceAmountCents: $priceRange?->min?->amountCents,
            maxPriceAmountCents: $priceRange?->max?->amountCents,
            availableSpaces: $availableTickets?->getFreeSpaces($bookingStats) ?? 0,
            bookingStats: $bookingStats,
        ));
    }
}
