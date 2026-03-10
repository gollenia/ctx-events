<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Application\DTOs\EventCacheSnapshot;
use Contexis\Events\Event\Domain\EventCacheRepository;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Domain\Contracts\Clock;

class EventHooks
{

	private const AVAILABILITY_KEYS = [
		EventMeta::TICKETS,
		EventMeta::BOOKING_CAPACITY,
		EventMeta::BOOKING_ENABLED,
		EventMeta::BOOKING_START,
		EventMeta::BOOKING_END,
		EventMeta::EVENT_START,
		EventMeta::BOOKING_CURRENCY,
	];

    public function __construct(
        private EventRepository $eventRepository,
        private EventCacheRepository $eventCacheRepository,
        private Clock $clock,
    ) {
    }

    public function register(): void
    {
        add_action('updated_post_meta', [$this, 'saveMetaData'], 10, 4);
		add_action('added_post_meta', [$this, 'saveMetaData'], 10, 4);
    }

    public function saveMetaData(int $meta_id, int $post_id, string $meta_key, mixed $meta_value): void
    {
        if (get_post_type($post_id) !== 'ctx-event') {
            return;
        }

		if (!in_array($meta_key, self::AVAILABILITY_KEYS, true)) {
			return;
		}

		$eventId = EventId::from($post_id);
		$event = $this->eventRepository->find($eventId);

		if ($event === null) {
			return;
		}

		$now = $this->clock->now();
		$priceRange = $event->tickets?->getEnabledTickets()->getPriceRange($now);

		$this->eventCacheRepository->saveCache(new EventCacheSnapshot(
			eventId: $post_id,
			minPriceAmountCents: $priceRange?->min?->amountCents,
			maxPriceAmountCents: $priceRange?->max?->amountCents,
		));
    }
}
