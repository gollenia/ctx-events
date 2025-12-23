<?php

declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Event\Application\EventCriteria;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventCollection;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\EventId;

class FakeEventRepository implements EventRepository
{
    public function __construct(
        private readonly ?Event $event
    ) {
    }

    public function find(?EventId $id): ?Event
    {
        return $this->event;
    }

    public function get(?EventId $id): Event
    {
        return $this->event;
    }

    public function first(EventCriteria $criteria): ?Event
    {
        return $this->event;
    }

    public function search(EventCriteria $criteria): EventCollection
    {
        return new EventCollection($this->event);
    }

    public function count(EventCriteria $criteria): int
    {
        return 1;
    }
}

