<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Domain\Signals\EventCapacityChanged;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Domain\Contracts\SignalDispatcher;

class EventHooks
{
    public function __construct(
        public SignalDispatcher $signalDispatcher,
    ) {
    }

    public function register(): void
    {
        add_action('updated_post_meta', [$this, 'saveMetaData'], 10, 4);
    }

    public function saveMetaData(int $meta_id, int $post_id, string $meta_key, mixed $meta_value): void
    {
        if (get_post_type($post_id) !== 'ctx-event') {
            return;
        }

        if ($meta_key !== EventMeta::BOOKING_CAPACITY) {
            return;
        }

        $this->signalDispatcher->dispatch(new EventCapacityChanged(EventId::from($post_id)));
    }
}
