<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Application\DTOs\EventCriteria;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventCollection;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventStatusCounts;
use Contexis\Events\Event\Domain\ValueObjects\EventSpaces;
use Contexis\Events\Shared\Application\ValueObjects\Pagination;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

class WpEventRepository implements EventRepository
{
    public function __construct(
        private EventMapper $mapper,
		private Clock $clock

    ) {
    }

    public function first(EventCriteria $criteria): ?Event
    {
        $builder = WpEventQueryBuilder::fromCriteria($criteria)
            ->withCache()
            ->addArg('posts_per_page', 1)
            ->addArg('no_found_rows', true);

        $wpq = new \WP_Query($builder->getArgs());

        return $wpq->have_posts()
            ? $this->mapper->map(new PostSnapshot($wpq->posts[0]))
            : null;
    }

    public function search(EventCriteria $criteria): EventCollection
    {
        $builder = WpEventQueryBuilder::fromCriteria($criteria)
            ->withCache();

        $query = new \WP_Query($builder->getArgs());

        $pagination = Pagination::of(
            totalItems: (int)$query->found_posts,
            currentPage: $criteria->page,
            perPage: $criteria->perPage
        );

        $items = [];

        foreach ($query->posts as $post) {
            $items[] = $this->mapper->map(new PostSnapshot($post));
        }

        $events = EventCollection::fromArray($items)
          ->withPagination($pagination);

        return $events;
    }

    public function count(EventCriteria $criteria): int
    {
        $builder = WpEventQueryBuilder::fromCriteria($criteria)
            ->withCache()
            ->addArg('fields', 'ids')
            ->addArg('no_found_rows', false)
            ->addArg('posts_per_page', -1);

        $wpq = new \WP_Query($builder->getArgs());

        return (int)$wpq->found_posts;
    }

    public function find(?EventId $id): ?Event
    {
        if ($id === null) {
            return null;
        }

        $post = get_post($id->toInt());

        if ($post === null || $post->post_type !== EventPost::POST_TYPE) {
            return null;
        }

        return $this->mapper->map(new PostSnapshot($post));
    }

    public function get(?EventId $id): Event
    {
        $event = $this->find($id);
        if ($event === null) {
            throw new \InvalidArgumentException("Event with ID {$id->toInt()} not found.");
        }
        return $event;
    }

	public function saveCache(Event $event): void
	{
		$now = $this->clock->now();
		$post_id = $event->id->toInt();
		$priceRange = $event->getAvailableTickets($now)?->getPriceRange($now);
		update_post_meta($post_id, EventMeta::CACHED_MIN_PRICE, $priceRange?->min->amountCents);
        update_post_meta($post_id, EventMeta::CACHED_MAX_PRICE, $priceRange?->max->amountCents);
		update_post_meta($post_id, EventMeta::CACHED_AVAILABLE, $event->getAvailableTickets($now)?->count() ?? 0);
		update_post_meta($post_id, EventMeta::CACHED_BOOKING_STATS, $event->ticketBookingsMap?->jsonSerialize());
	}

	public function saveStatus(Event $event): void
	{
		wp_update_post([
			'ID' => $event->id->toInt(),
			'post_status' => $event->status->value
		]);
	}

	public function getCountsByStatus(): EventStatusCounts
	{
		$counts = wp_count_posts(EventPost::POST_TYPE); 
    
		return new EventStatusCounts(
			draft: (int) ($counts->draft ?? 0),
			publish: (int) ($counts->publish ?? 0),
			future: (int) ($counts->future ?? 0),
			pending: (int) ($counts->pending ?? 0),
			private: (int) ($counts->private ?? 0),
			trash: (int) ($counts->trash ?? 0),
			cancelled: (int) ($counts->cancelled ?? 0),
		);
	}
}
