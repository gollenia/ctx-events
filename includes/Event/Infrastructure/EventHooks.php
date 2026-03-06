<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Domain\Signals\EventAvailabilityChanged;
use Contexis\Events\Event\Domain\Signals\EventCapacityChanged;
use Contexis\Events\Event\Domain\Signals\EventTicketsChanged;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Domain\Contracts\SignalDispatcher;

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
        public SignalDispatcher $signalDispatcher,
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

		$eventId = EventId::from($post_id);

		if(in_array($meta_key, self::AVAILABILITY_KEYS, true)) {
				$this->signalDispatcher->dispatch(new EventAvailabilityChanged($eventId));
		};

    }
}
