<?php

namespace Contexis\Events\Infrastructure\Persistence;

use Contexis\Events\Domain\Collections\EventCollection;
use Contexis\Events\Domain\Models\Event;
use Contexis\Events\Domain\Repositories\EventRepository;
use Contexis\Events\Domain\ValueObjects\Id\EventId;
use Contexis\Events\Infrastructure\PostTypes\EventPost;
use Contexis\Events\Infrastructure\Persistence\Mappers\EventMapper;
use Contexis\Events\Infrastructure\Persistence\Query\WpEventQuery;
use Contexis\Events\Infrastructure\PostTypes\PostSnapshot;

class WpEventRepository extends WpAbstractRepository implements EventRepository
{
    public const POST_TYPE_CLASS = \Contexis\Events\Infrastructure\PostTypes\EventPost::class;

    public function get(): EventCollection
    {
        $posts = parent::get();
        $events = new EventCollection();
        foreach ($posts as $key => $post) {
            $events->add(EventMapper::map(new PostSnapshot($post)));
        }
        return $events;
    }


	

    public function find(?EventId $id): ?Event
    {
        $post = parent::getSnapshot($id);
        if ($post->post_type !== EventPost::POST_TYPE) {
            return null;
        }
        return $post ? EventMapper::map($post) : null;
    }

    public function first(): ?Event
    {
        $post = parent::first();
        if (!$post) {
            return null;
        }
        return EventMapper::map($post);
    }
}
