<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\Contracts;

use Contexis\Events\Payment\Domain\Transaction;

interface FindReconcilableTransactions
{
    /** @return Transaction[] */
    public function findPendingForReconciliation(\DateTimeImmutable $now): array;
}
