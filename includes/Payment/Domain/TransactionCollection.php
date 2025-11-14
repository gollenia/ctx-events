<?php

namespace Contexis\Events\Payment\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final class TransactionCollection extends Collection
{
    public function __construct(Transaction ...$transactions)
    {
        $this->items = $transactions;
    }
}
