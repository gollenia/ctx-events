<?php

namespace Contexis\Events\Domain\ValueObjects;

final class TermCollection implements \IteratorAggregate, \Countable {
	
	/**
	 * @param Term[] $terms
	 */
	private array $items;

	public function __construct(
		array $items
	) {
		$this->items = $items;
	}

	public function getIterator(): \Traversable {
		return new \ArrayIterator($this->items);
	}

	public function count(): int {
		return count($this->items);
	}

	public function slugs(): array {
		return array_map(fn(Term $t) => $t->slug, $this->items);
	}

	public function names(): array {
		return array_map(fn(Term $t) => $t->name, $this->items);
	}

	public function has(string $slug): bool {
		foreach ($this->items as $term) {
			if ($term->slug === $slug) return true;
		}
		return false;
	}
}