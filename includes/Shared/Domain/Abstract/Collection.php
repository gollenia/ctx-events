<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\Abstract;

use Contexis\Events\Shared\Application\ValueObjects\Pagination;
use Contexis\Events\Shared\Domain\Contracts\StatusCountsInterface;

/** @phpstan-ignore missingType.generics */
abstract readonly class Collection implements \Countable, \IteratorAggregate
{
	/**
	 * @param array<mixed> $items
	 * @param StatusCountsInterface|null $statusCounts
	 * @param Pagination|null $pagination
	 */
    final protected function __construct(
		protected array $items, 
		protected ?Pagination $pagination = null,
		protected ?StatusCountsInterface $statusCounts = null
	)
    {
    }

    public function withPagination(Pagination $pagination): static
    {
        return clone($this, ['pagination' => $pagination]);
    }

	public function withStatusCounts(StatusCountsInterface $statusCounts): static
    {
        return clone($this, ['statusCounts' => $statusCounts]);
    }

	public function hasStatusCounts(): bool
    {
        return $this->statusCounts !== null;
    }

    public function withEnrichment(\Closure $enrichmentCallback): static
    {
        $enrichedItems = array_map($enrichmentCallback, $this->items);
        return clone($this, ['items' => $enrichedItems]);
    }

    public function pagination(): ?Pagination
    {
        return $this->pagination;
    }

	public function statusCounts(): ?StatusCountsInterface
	{
		return $this->statusCounts;
	}

	public static function empty() : static
	{
		return new static([]);
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

	/**
	 * @return array<mixed>
	 */
    public function map(callable $callback): array
    {
        return array_map($callback, $this->items);
    }

	/**
	 * @return array<mixed>
	 */
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
