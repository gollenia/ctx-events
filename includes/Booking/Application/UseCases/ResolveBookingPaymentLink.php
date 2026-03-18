<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\GatewayRepository;
use Contexis\Events\Payment\Domain\PaymentGateway;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Payment\Domain\TransactionRepository;
use Contexis\Events\Shared\Domain\Contracts\Clock;

final class ResolveBookingPaymentLink
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private TransactionRepository $transactionRepository,
        private GatewayRepository $gatewayRepository,
        private Clock $clock,
    ) {
    }

    public function execute(string $reference): Transaction
    {
        $booking = $this->bookingRepository->findByReference($reference);

        if ($booking === null) {
            throw new \DomainException("Booking with reference {$reference} not found");
        }

        $bookingId = $booking->id;
        if ($bookingId === null) {
            throw new \RuntimeException('Booking has no ID.');
        }

        if ($booking->priceSummary->isFree()) {
            throw new \DomainException('Free bookings do not require a payment link.');
        }

        $gatewayId = trim((string) $booking->gateway);
        if ($gatewayId === '') {
            throw new \DomainException('Booking has no payment gateway configured.');
        }

        $gateway = $this->gatewayRepository->find($gatewayId);
        if ($gateway === null) {
            throw new \DomainException("Payment gateway not found: {$gatewayId}");
        }

        $reusableTransaction = $this->findReusableTransaction($bookingId, $gateway);
        if ($reusableTransaction !== null) {
            return $reusableTransaction;
        }

        $transaction = $gateway->initiatePayment($booking);
        if ($transaction->checkoutUrl === null) {
            throw new \DomainException('Payment gateway does not provide an online checkout URL.');
        }

        $this->transactionRepository->save($transaction);

        return $transaction;
    }

    private function findReusableTransaction(BookingId $bookingId, PaymentGateway $gateway): ?Transaction
    {
        $transactions = $this->transactionRepository->findByBookingId($bookingId)->toArray();

        usort(
            $transactions,
            static fn(Transaction $left, Transaction $right): int =>
                $right->createdAt <=> $left->createdAt
        );

        $now = $this->clock->now();

        foreach ($transactions as $transaction) {
            if ($transaction->checkoutUrl === null) {
                continue;
            }

            $resolved = $transaction;

            if ($transaction->status === TransactionStatus::PENDING) {
                $resolved = $gateway->verifyPayment($transaction);
                if ($this->transactionChanged($transaction, $resolved)) {
                    $this->transactionRepository->save($resolved);
                }
            }

            if ($resolved->status === TransactionStatus::PAID) {
                throw new \DomainException('Booking has already been paid.');
            }

            if ($resolved->status !== TransactionStatus::PENDING) {
                continue;
            }

            if ($resolved->hasExpiredAt($now)) {
                $expired = $resolved->expire();
                $this->transactionRepository->save($expired);
                continue;
            }

            return $resolved;
        }

        return null;
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
