<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Application\DTOs\EventCriteria;
use Contexis\Events\Event\Application\DTOs\EventCacheSnapshot;
use Contexis\Events\Event\Domain\EventCacheRepository;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventCollection;
use Contexis\Events\Event\Domain\EventStatusRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventStatusCounts;
use Contexis\Events\Event\Domain\ValueObjects\EventSpaces;
use Contexis\Events\Event\Infrastructure\Mappers\EventMapper;
use Contexis\Events\Shared\Application\ValueObjects\Pagination;
use Contexis\Events\Shared\Infrastructure\Wordpress\InteractsWithStatusCounts;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

class WpEventRepository implements EventRepository, EventStatusRepository, EventCacheRepository
{
    use InteractsWithStatusCounts;

    public function __construct(
        private EventMapper $mapper

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

        $events = EventCollection::from(...$items)
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

	public function saveCache(EventCacheSnapshot $snapshot): void
	{
		$postId = (int) $snapshot->eventId;

		update_post_meta($postId, EventMeta::CACHED_MIN_PRICE, (int) $snapshot->minPriceAmountCents);
		update_post_meta($postId, EventMeta::CACHED_MAX_PRICE, (int) $snapshot->maxPriceAmountCents);
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
        $baseCounts = $this->mapWpCountsToStatusCounts($counts);

		return new EventStatusCounts(
            draft: $baseCounts->draft,
            publish: $baseCounts->publish,
            future: $baseCounts->future,
            pending: $baseCounts->pending,
            private: $baseCounts->private,
            trash: $baseCounts->trash,
			cancelled: (int) ($counts->cancelled ?? 0),
		);
	}
}
