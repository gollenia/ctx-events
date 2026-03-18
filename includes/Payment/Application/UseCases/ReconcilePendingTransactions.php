<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\UseCases;

use Contexis\Events\Payment\Application\Contracts\FindReconcilableTransactions;
use Contexis\Events\Payment\Application\Services\SyncBookingFromTransaction;
use Contexis\Events\Payment\Domain\GatewayRepository;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Payment\Domain\TransactionRepository;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\ValueObjects\Actor;

final class ReconcilePendingTransactions
{
    public function __construct(
        private FindReconcilableTransactions $findReconcilableTransactions,
        private TransactionRepository $transactionRepository,
        private GatewayRepository $gatewayRepository,
        private SyncBookingFromTransaction $syncBookingFromTransaction,
        private Clock $clock,
    ) {
    }

    public function execute(): int
    {
        $now = $this->clock->now();
        $transactions = $this->findReconcilableTransactions->findPendingForReconciliation($now);
        $updated = 0;

        foreach ($transactions as $transaction) {
            $resolvedTransaction = $this->resolveTransaction($transaction, $now);

            if ($resolvedTransaction->status === $transaction->status) {
                continue;
            }

            $this->transactionRepository->save($resolvedTransaction);
            $this->syncBookingFromTransaction->execute(
                $resolvedTransaction,
                Actor::system('Transaction reconciliation')
            );
            $updated++;
        }

        return $updated;
    }

    private function resolveTransaction(Transaction $transaction, \DateTimeImmutable $now): Transaction
    {
        if ($transaction->hasExpiredAt($now)) {
            return $transaction->expire();
        }

        $gateway = $this->gatewayRepository->find($transaction->gateway);

        if ($gateway === null) {
            return $transaction;
        }

        return $gateway->verifyPayment($transaction);
    }
}
