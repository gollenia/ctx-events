<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\Services;

use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Payment\Domain\TransactionRepository;

final class SyncOfflineTransactionForBookingAction
{
    public function __construct(private TransactionRepository $transactionRepository)
    {
    }

    public function markPaid(Booking $booking): void
    {
        $transaction = $this->getOfflineTransaction($booking);

        if ($transaction === null) {
            return;
        }

        $this->transactionRepository->save($transaction->complete());
    }

    public function markCanceled(Booking $booking): void
    {
        $transaction = $this->getOfflineTransaction($booking);

        if ($transaction === null) {
            return;
        }

        $this->transactionRepository->save($transaction->cancel());
    }

    public function markPending(Booking $booking): void
    {
        $transaction = $this->getOfflineTransaction($booking);

        if ($transaction === null) {
            return;
        }

        $this->transactionRepository->save($transaction->pend());
    }

    private function getOfflineTransaction(Booking $booking): ?\Contexis\Events\Payment\Domain\Transaction
    {
        if ($booking->id === null) {
            return null;
        }

        $transaction = $this->transactionRepository->findLatestByBookingId($booking->id);

        if ($transaction === null || !$transaction->isOffline()) {
            return null;
        }

        return $transaction;
    }
}
