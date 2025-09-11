<?php

namespace Contexis\Events\Collections;

use Contexis\Events\Models\Transaction;

class TransactionCollection implements \IteratorAggregate, \Countable
{
	/** @var list<Transaction> */
	public array $items = [];

	public function __construct(array $items = []) { $this->items = $items; }

	public function add(Transaction $item): void {
		$this->items[] = $item;
	}

	public function remove(Transaction $item): void {
		$this->items = array_filter(
			$this->items,
			fn($i) => $i !== $item
		);
	
		$this->items = array_values($this->items);
	}

	public function to_array(): array {
		return array_map(fn($item) => $item->to_array(), $this->items);
	}

	public function getIterator(): \Traversable { yield from $this->items; }

	public function count(): int { return \count($this->items); }

	public static function from_array(array $data): TransactionCollection {
		$instance = new self();
		foreach ($data as $itemData) {
			try {
				$instance->add(Transaction::fromArray($itemData));
			} catch (\InvalidArgumentException $e) {
				// Handle invalid data (e.g., log it, skip it, etc.)
			}
		}
		return $instance;
	}
}