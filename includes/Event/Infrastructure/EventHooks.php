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
    public function __construct(
        private EventRepository $eventRepository,
        private EventCacheRepository $eventCacheRepository,
        private Clock $clock,
    ) {
    }

    public function register(): void
    {
        add_action('save_post_' . EventPost::POST_TYPE, [$this, 'savePost'], 10, 3);
        add_action('rest_after_insert_' . EventPost::POST_TYPE, [$this, 'saveRestPost'], 10, 3);
    }

    public function savePost(int $post_id, \WP_Post $post, bool $update): void
    {
        if ($post->post_type !== EventPost::POST_TYPE) {
            return;
        }

        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        $this->refreshCache($post_id);
    }

    public function saveRestPost(\WP_Post $post, \WP_REST_Request $request, bool $creating): void
    {
        if ($post->post_type !== EventPost::POST_TYPE) {
            return;
        }

        $this->refreshCache((int) $post->ID);
    }

    private function refreshCache(int $post_id): void
    {
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
