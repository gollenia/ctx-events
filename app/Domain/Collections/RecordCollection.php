<?php

namespace Contexis\Events\Domain\Collections;

use Contexis\Events\Domain\Models\Record;
final class RecordCollection implements \Countable, \IteratorAggregate {

	/** @var Record[] */
	private array $records = [];

	public function __construct(Record ...$records) {
		$this->records = $records;
	}

	public function getIterator(): \Traversable {
		return new \ArrayIterator($this->records);
	}

	public function count(): int {
		return count($this->records);
	}

	public function add(Record $item): void {
		$this->records[] = $item;
	}

	public function delete(int $index): void {
		unset($this->records[$index]);
		$this->records = array_values($this->records);
	}
}