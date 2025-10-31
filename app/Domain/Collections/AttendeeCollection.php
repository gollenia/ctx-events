<?php

namespace Contexis\Events\Domain\Collections;

use Contexis\Events\Domain\Models\Attendee;

final class AttendeeCollection implements \Countable, \IteratorAggregate {

	/** @var Attendee[] */
	private array $attendees = [];

	public function __construct(Attendee ...$attendees) {
		$this->attendees = $attendees;
	}

	public function getIterator(): \Traversable {
		return new \ArrayIterator($this->attendees);
	}

	public function count(): int {
		return count($this->attendees);
	}
}