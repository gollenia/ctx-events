<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\Services;

use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\GatewayRepository;
use Contexis\Events\Payment\Domain\TransactionRepository;

/** Cancels payable requests only. Paid transactions remain evidence for external refunds. */
final class CancelOpenBookingTransactions
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private GatewayRepository $gatewayRepository,
    ) {
    }

    public function execute(Booking $booking): void
    {
        if ($booking->id === null) {
            return;
        }

        foreach ($this->transactionRepository->findByBookingId($booking->id) as $transaction) {
            if ($transaction->status !== TransactionStatus::PENDING) {
                continue;
            }

            $gateway = $this->gatewayRepository->find($transaction->gateway);
            if ($gateway === null) {
                throw new \DomainException("Payment gateway not found: {$transaction->gateway}");
            }

            $this->transactionRepository->save($gateway->cancelPayment($transaction));
        }
    }
}
