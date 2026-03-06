<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\Abstract;

use Contexis\Events\Shared\Application\ValueObjects\Pagination;

abstract readonly class Collection implements \Countable, \IteratorAggregate
{
    /** @var array */
    protected array $items;
    protected ?Pagination $pagination;

    public function __construct(object ...$items)
    {
        $this->items = $items;
    }

    public function withPagination(Pagination $pagination): static
    {
        return clone($this, ['pagination' => $pagination]);
    }

    public function pagination(): ?Pagination
    {
        return $this->pagination;
    }

    public static function fromArray(array $items): static
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

	public function filter(callable $callback): static
	{
		return new static(...array_filter($this->items, $callback));
	}

	public function first(): ?object
    {
        return $this->items[0] ?? null;
    }

    public function map(callable $callback): array
    {
        return array_map($callback, $this->items);
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
