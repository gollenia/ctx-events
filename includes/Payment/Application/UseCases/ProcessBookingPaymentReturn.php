<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\UseCases;

use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Payment\Application\Services\SyncBookingFromTransaction;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\GatewayRepository;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Payment\Domain\TransactionRepository;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\ValueObjects\Actor;

final class ProcessBookingPaymentReturn
{
    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly TransactionRepository $transactionRepository,
        private readonly GatewayRepository $gatewayRepository,
        private readonly SyncBookingFromTransaction $syncBookingFromTransaction,
        private readonly Clock $clock,
    ) {
    }

    /**
     * @return array{booking: Booking, transaction: ?Transaction}
     */
    public function execute(string $reference): array
    {
        $booking = $this->bookingRepository->findByReference($reference);

        if ($booking === null) {
            throw new \DomainException("Booking with reference {$reference} not found.");
        }

        $bookingId = $booking->id;
        if ($bookingId === null) {
            throw new \RuntimeException('Booking has no ID.');
        }

        $transaction = $this->transactionRepository->findLatestByBookingId($bookingId);
        if ($transaction === null) {
            return [
                'booking' => $booking,
                'transaction' => null,
            ];
        }

        $resolved = $this->resolveTransaction($transaction);
        if ($this->transactionChanged($transaction, $resolved)) {
            $this->transactionRepository->save($resolved);
            $this->syncBookingFromTransaction->execute(
                $resolved,
                Actor::system('Payment return'),
            );
        }

        return [
            'booking' => $booking,
            'transaction' => $resolved,
        ];
    }

    private function resolveTransaction(Transaction $transaction): Transaction
    {
        if ($transaction->status !== TransactionStatus::PENDING) {
            return $transaction;
        }

        if ($transaction->hasExpiredAt($this->clock->now())) {
            return $transaction->expire();
        }

        $gateway = $this->gatewayRepository->find($transaction->gateway);
        if ($gateway === null) {
            return $transaction;
        }

        return $gateway->verifyPayment($transaction);
    }

    private function transactionChanged(Transaction $original, Transaction $resolved): bool
    {
        return $original->status !== $resolved->status
            || $this->formatDate($original->expiresAt) !== $this->formatDate($resolved->expiresAt);
    }

    private function formatDate(?\DateTimeImmutable $value): ?string
    {
        return $value?->format(DATE_ATOM);
    }
}
