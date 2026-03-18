<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\Services;

use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\Enums\BookingEvent;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntry;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Payment\Domain\TransactionRepository;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\ValueObjects\Actor;

final class SyncBookingFromTransaction
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private EventRepository $eventRepository,
        private TransactionRepository $transactionRepository,
        private Clock $clock,
    ) {
    }

    public function execute(Transaction $transaction, Actor $actor): void
    {
        $booking = $this->bookingRepository->find($transaction->bookingId);

        if ($booking === null) {
            throw new \DomainException("Booking not found for transaction: {$transaction->externalId}");
        }

        $event = $this->eventRepository->find($booking->eventId);

        if ($event !== null && $event->isPast($this->clock->now())) {
            return;
        }

        if (!$this->shouldAffectBooking($transaction, $booking->status)) {
            return;
        }

        $targetStatus = match ($transaction->status) {
            TransactionStatus::PAID => BookingStatus::APPROVED,
            TransactionStatus::EXPIRED,
            TransactionStatus::FAILED,
            TransactionStatus::CANCELED => BookingStatus::EXPIRED,
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
                actor: $actor,
                timestamp: $this->clock->now(),
            ));

        $bookingId = $booking->id ?? throw new \RuntimeException('Booking has no ID');
        $this->bookingRepository->updateStatus($bookingId, $targetStatus, $updatedBooking->logEntries);
    }

    private function shouldAffectBooking(Transaction $transaction, BookingStatus $bookingStatus): bool
    {
        if ($bookingStatus === BookingStatus::APPROVED && $transaction->status !== TransactionStatus::PAID) {
            return false;
        }

        $transactions = $this->transactionRepository->findByBookingId($transaction->bookingId)->toArray();
        $relevantTransactions = array_values(array_filter(
            $transactions,
            static fn(Transaction $candidate): bool => self::isRelevantStatus($candidate->status)
        ));

        if ($relevantTransactions === []) {
            return true;
        }

        $mostRecentRelevant = $relevantTransactions[0];

        if ($mostRecentRelevant->externalId !== $transaction->externalId) {
            return false;
        }

        if ($transaction->status !== TransactionStatus::PAID && $mostRecentRelevant->status === TransactionStatus::PAID) {
            return false;
        }

        return true;
    }

    private static function isRelevantStatus(TransactionStatus $status): bool
    {
        return in_array($status, [TransactionStatus::PENDING, TransactionStatus::PAID], true);
    }
}
