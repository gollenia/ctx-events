<?php

namespace Contexis\Events\Domain\Collections;

use Contexis\Events\Domain\Models\Transaction;

final class TransactionCollection extends AbstractTypedCollection {

	public function __construct(Transaction ...$transactions) {
		$this->items = $transactions;
	}

}