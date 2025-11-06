<?php

namespace Contexis\Events\Domain\Collections;

abstract class AbstractCollection implements \Countable, \IteratorAggregate
{
    /** @var array */
    protected array $items = [];
    public string $order = 'asc';
    public string $orderBy = 'date';

    public function __construct(object ...$items)
    {
        $this->items = $items;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function add(object $item): void
    {
        $this->items[] = $item;
    }

    public function join(EventCollection $other): self
    {
        if ($this->orderBy !== $other->orderBy || $this->order !== $other->order) {
            throw new \InvalidArgumentException('Cannot join collections with different sorting options.');
        }

        array_push($this->items, ...$other->items);
        return $this;
    }


    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}
