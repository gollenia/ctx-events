<?php

namespace Contexis\Events\Event\Application;

use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\EventCollection;
use Contexis\Events\Shared\Application\Contracts\DTO;

final class EventListDto implements DTO, \Countable, \IteratorAggregate
{
    /**
     * @param EventDto[] $items
     */
    public function __construct(
        public readonly array $items,
        public readonly int $total,
        public readonly int $pages,
        public readonly int $perPage,
    ) {
    }

    public static function fromCollection(EventCollection $collection): self
    {
        $items = [];

        foreach ($collection as $event) {
            $items[] = EventDto::fromDomainModel($event);
        }

        return new self(
            items: $items,
            total: $collection->getTotalItems(),      // hier nutzt du deine Metadaten
            pages: $collection->getTotalPages(),
            perPage: $collection->getPerPage(),
        );
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): \Traversable
    {
        yield from $this->items;
    }
}
