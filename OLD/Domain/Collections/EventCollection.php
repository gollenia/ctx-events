<?php

namespace Contexis\Events\Domain\Collections;
use WP_Query;
use Countable;
use IteratorAggregate;
use Contexis\Events\Models\Event;
use Contexis\Events\PostTypes\EventPost;

class EventCollection implements Countable, IteratorAggregate {
	
	/** @var Event[] */
	protected array $events = [];

	public function count( ) : int {
		return count($this->events);
	}
	
	public function getIterator(): \Traversable {
        return new \ArrayIterator($this->events);
    }

	
}
?>