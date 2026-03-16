<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Payment\Application\Contracts\FindReconcilableTransactions;
use Contexis\Events\Payment\Domain\Transaction;

final class FakeReconcilableTransactionFinder implements FindReconcilableTransactions
{
    /** @param Transaction[] $transactions */
    public function __construct(private array $transactions)
    {
    }

    public function findPendingForReconciliation(\DateTimeImmutable $now, \DateTimeImmutable $staleBefore): array
    {
        return array_values(array_filter(
            $this->transactions,
            static fn(Transaction $transaction): bool => $transaction->status === \Contexis\Events\Payment\Domain\Enums\TransactionStatus::PENDING
                && ($transaction->hasExpiredAt($now) || $transaction->createdAt <= $staleBefore)
        ));
    }
}
