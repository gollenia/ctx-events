<?php

namespace Contexis\Events\Domain\Collections;

use Contexis\Events\Domain\Models\Event;

final class EventCollection implements \Countable, \IteratorAggregate
{
	/** @var Event[] */
	private array $events;


	public function __construct(Event ...$events) {
		$this->events = $events;
	}

	public function getIterator(): \Traversable {
		return new \ArrayIterator($this->events);
	}

	public function count(): int {
		return count($this->events);
	}
}