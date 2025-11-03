<?php

namespace Contexis\Events\Domain\Collections;

abstract class AbstractTypedCollection implements \Countable, \IteratorAggregate {

	/** @var array */
	protected array $items = [];

	public function __construct(object ...$items) {
		$this->items = $items;
	}

	public function getIterator(): \Traversable {
		return new \ArrayIterator($this->items);
	}

	public function count(): int {
		return count($this->items);
	}

	public function is_empty(): bool {
		return empty($this->items);
	}
}