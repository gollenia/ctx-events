<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\UseCases;

use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\Enums\BookingEvent;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntry;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\GatewayRepository;
use Contexis\Events\Payment\Domain\TransactionRepository;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\Contracts\CurrentActorProvider;

final class SyncTransactionStatus
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private GatewayRepository $gatewayRepository,
        private BookingRepository $bookingRepository,
        private Clock $clock,
        private CurrentActorProvider $currentActorProvider,
    ) {
    }

    public function execute(string $externalId): void
    {
        $transaction = $this->transactionRepository->findByExternalId($externalId);

        if ($transaction === null) {
            throw new \DomainException("Transaction not found: {$externalId}");
        }

        $gateway = $this->gatewayRepository->find($transaction->gateway);

        if ($gateway === null) {
            throw new \DomainException("Payment gateway not found: {$transaction->gateway}");
        }

        $verifiedTransaction = $gateway->verifyPayment($transaction);
        $this->transactionRepository->save($verifiedTransaction);

        $booking = $this->bookingRepository->find($verifiedTransaction->bookingId);

        if ($booking === null) {
            throw new \DomainException("Booking not found for transaction: {$externalId}");
        }

        $targetStatus = match ($verifiedTransaction->status) {
            TransactionStatus::PAID => BookingStatus::APPROVED,
            TransactionStatus::EXPIRED => BookingStatus::EXPIRED,
            default => null,
        };

        if ($targetStatus === null || $booking->status === $targetStatus) {
            return;
        }

        if (!$booking->status->canTransitionTo($targetStatus)) {
            return;
        }

        $eventType = $targetStatus === BookingStatus::APPROVED
            ? BookingEvent::Approved
            : BookingEvent::Updated;

        $updatedBooking = $booking
            ->withBookingStatus($targetStatus)
            ->appendLogEntry(new LogEntry(
                eventType: $eventType,
                actor: $this->currentActorProvider->current(),
                timestamp: $this->clock->now(),
            ));

        $bookingId = $booking->id ?? throw new \RuntimeException('Booking has no ID');
        $this->bookingRepository->updateStatus($bookingId, $targetStatus, $updatedBooking->logEntries);
    }
}
