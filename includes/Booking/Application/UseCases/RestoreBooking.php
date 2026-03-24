<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\Contracts\BookingAction;
use Contexis\Events\Booking\Application\DTOs\BookingActionRequest;
use Contexis\Events\Booking\Application\Services\SyncOfflineTransactionForBookingAction;
use Contexis\Events\Communication\Application\DTOs\BookingEmailResult;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\Enums\BookingLogEvent;
use Contexis\Events\Booking\Domain\Enums\BookingLogLevel;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntry;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\Contracts\CurrentActorProvider;

final class RestoreBooking implements BookingAction
{
    public function __construct(
        private BookingRepository $repository,
        private SyncOfflineTransactionForBookingAction $transactionSync,
        private Clock $clock,
        private CurrentActorProvider $currentActorProvider,
    ) {
    }

    public function execute(BookingActionRequest $request): BookingEmailResult
    {
        $booking = $this->repository->findByReference($request->reference);

        if ($booking === null) {
            throw new \DomainException("Booking not found: {$request->reference}");
        }

        if (!$booking->status->canTransitionTo(BookingStatus::PENDING)) {
            throw new \DomainException(
                "Cannot transition from {$booking->status->name} to " . BookingStatus::PENDING->name
            );
        }

        $id = $booking->id ?? throw new \RuntimeException('Booking has no ID');
        $updatedBooking = $booking
            ->withBookingStatus(BookingStatus::PENDING)
            ->appendLogEntry(new LogEntry(
                eventType: BookingLogEvent::Restored,
                level: BookingLogLevel::Info,
                actor: $this->currentActorProvider->current(),
                timestamp: $this->clock->now(),
            ));

        $this->repository->updateStatus($id, BookingStatus::PENDING, $updatedBooking->logEntries);
        $this->transactionSync->markPending($booking);

        return BookingEmailResult::empty();
    }
}
