<?php

declare(strict_types = 1);

namespace Contexis\Events\Event\Application\Subscribers;

use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\Signals\TicketPriceChangedSignal;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PriceCacheSubscriber implements EventSubscriberInterface
{
	public function __construct(
        private readonly EventRepository $repository
    ) {}

	public static function getSubscribedEvents(): array
    {
        return [
            TicketPriceChangedSignal::class => 'updatePriceCache',
        ];
    }

	public function updatePriceCache(TicketPriceChangedSignal $signal): void
    {
        $event = $this->repository->find($signal->eventId);
		if (!$event) return;

		$ticketAvailabilities = $event->getAvailabilitySnapshot($stats, $this->clock);

		$minPrice = null;

		foreach ($ticketAvailabilities as $ticketAvailability) {
			if(!$ticketAvailability->isAvailable()) continue;
			if ($minPrice === null || $ticketAvailability->getTicketPrice()->amount() < $minPrice) {
				$minPrice = $ticketAvailability->getTicketPrice()->amount();
			}
		}
    }
}