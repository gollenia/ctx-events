<?php

namespace Contexis\Events\Domain\Collections;

use Contexis\Events\Domain\Models\Transaction;

final class TransactionCollection implements \Countable, \IteratorAggregate {

	/** @var Transaction[] */
	private array $transactions = [];

	public function __construct(Transaction ...$transactions) {
		$this->transactions = $transactions;
	}

	public function getIterator(): \Traversable {
		return new \ArrayIterator($this->transactions);
	}

	public function count(): int {
		return count($this->transactions);
	}
}