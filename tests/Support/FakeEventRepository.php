<?php

namespace Tests\Support;

use Contexis\Events\Domain\Repositories\EventRepository;
use Contexis\Events\Domain\Models\Event;
use Contexis\Events\Domain\Collections\EventCollection;
use Contexis\Events\Core\Contracts\QueryOptions;
use Contexis\Events\Domain\ValueObjects\Id\EventId;

final class FakeEventRepository implements EventRepository
{
    public ?Event $toReturn;
    public ?EventId $lastFindArg = null;

    public function __construct(?Event $toReturn = null)
    {
        $this->toReturn = $toReturn;
    }

    /**
     * Interface: public function find(?EventId $id): ?Event
     */
    public function find(?EventId $id): ?Event
    {
        $this->lastFindArg = $id;
        return $this->toReturn;
    }

    /**
     * Interface: public function query(QueryOptions $args): static
     */
    public function query(QueryOptions $args): static
    {
        return $this;
    }

    /**
     * Interface: public function first(): ?Event
     */
    public function first(): ?Event
    {
        return $this->toReturn;
    }

    /**
     * Interface: public function get(): EventCollection
     */
    public function get(): EventCollection
    {
        // In Unit-Tests meist nicht nötig, deshalb nur Platzhalter:
        throw new \LogicException('get() not needed in FakeEventRepository');
    }

    /**
     * Interface: public function count(): int
     */
    public function count(): int
    {
        return $this->toReturn ? 1 : 0;
    }
}
