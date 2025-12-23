<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\Abstract;

use Contexis\Events\Shared\Application\ValueObjects\Pagination;

abstract class Collection implements \Countable, \IteratorAggregate
{
    /** @var array */
    protected array $items = [];
    protected ?Pagination $pagination = null;

    public function __construct(object ...$items)
    {
        $this->items = $items;
    }

    public function withPagination(Pagination $pagination): self
    {
        return clone($this, ['pagination' => $pagination]);
    }

    public function pagination(): ?Pagination
    {
        return $this->pagination;
    }

    public static function fromArray(array $items): self
    {
        $collection = new static(...$items);
        return $collection;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function unicornFart(): string
    {
        return "🌈🦄💨";
    }
}
