<?php

namespace Contexis\Events\Infrastructure\Persistence;

use Contexis\Events\Application\Query\ListEventsQuery;
use Contexis\Events\Application\Requests\EventPageRequest;
use Contexis\Events\Application\Security\ViewContext;
use Contexis\Events\Domain\Collections\EventCollection;
use Contexis\Events\Domain\Models\Event;
use Contexis\Events\Domain\Repositories\EventRepository;
use Contexis\Events\Domain\ValueObjects\Id\EventId;
use Contexis\Events\Infrastructure\PostTypes\EventPost;
use Contexis\Events\Infrastructure\Persistence\Mappers\EventMapper;
use Contexis\Events\Infrastructure\Persistence\Query\WpEventQuery;
use Contexis\Events\Infrastructure\Persistence\Query\WpEventQueryBuilder;
use Contexis\Events\Infrastructure\PostTypes\PostSnapshot;

class WpEventRepository implements EventRepository
{
	public function __construct(
        private EventMapper $mapper
    ) {}

	public function first(EventPageRequest $request, ViewContext $viewContext): ?Event {
        $builder = WpEventQueryBuilder::fromRequest($request)
			->withCache()
			->addArg('posts_per_page', 1)
            ->addArg('no_found_rows', true);   
		
        $wpq = new \WP_Query($builder->getArgs());

        return $wpq->have_posts()
            ? $this->mapper->map(new PostSnapshot($wpq->posts[0]))
            : null;
    }

	public function search(EventPageRequest $request, ViewContext $viewContext): EventCollection {
		$builder = WpEventQueryBuilder::fromRequest($request)
			->withCache();

		$wpq = new \WP_Query($builder->getArgs());

		$events = new EventCollection();
		foreach ($wpq->posts as $post) {
			$events->add($this->mapper->map(new PostSnapshot($post)));
		}

		return $events;
	}

	public function count(EventPageRequest $request, ViewContext $viewContext): int {
		$builder = WpEventQueryBuilder::fromRequest($request)
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

	public function count(EventPageRequest $request, ViewContext $viewContext): int
	{
		$builder = WpEventQueryBuilder::fromRequest($request)
			->withCache()
			->addArg('fields', 'ids')
			->addArg('no_found_rows', false)
			->addArg('posts_per_page', -1);

		$wpq = new \WP_Query($builder->getArgs());

		return (int)$wpq->found_posts;
	}
}
