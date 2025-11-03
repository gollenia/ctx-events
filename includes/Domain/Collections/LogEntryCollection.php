<?php

namespace Contexis\Events\Domain\Collections;

use Contexis\Events\Domain\ValueObjects\LogEntry;
final class LogEntryCollection extends AbstractTypedCollection {

	public function __construct(LogEntry ...$logEntries) {
		$this->items = $logEntries;
	}

	public function add(LogEntry $item): void {
		$this->items[] = $item;
	}

	public function delete(int $index): void {
		unset($this->items[$index]);
		$this->items = array_values($this->items);
	}
}