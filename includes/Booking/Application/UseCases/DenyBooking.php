<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\Contracts\BookingAction;
use Contexis\Events\Booking\Application\DTOs\BookingActionRequest;
use Contexis\Events\Booking\Application\Services\SyncOfflineTransactionForBookingAction;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\Enums\BookingEvent;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntry;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\Contracts\CurrentActorProvider;

final class DenyBooking implements BookingAction
{
    public function __construct(
        private BookingRepository $repository,
        private SyncOfflineTransactionForBookingAction $transactionSync,
        private Clock $clock,
        private CurrentActorProvider $currentActorProvider,
    ) {
    }

    public function execute(BookingActionRequest $request): void
    {
        $booking = $this->repository->findByReference($request->reference);

        if ($booking === null) {
            throw new \DomainException("Booking not found: {$request->reference}");
        }

        if (!$booking->status->canTransitionTo(BookingStatus::CANCELED)) {
            throw new \DomainException(
                "Cannot transition from {$booking->status->name} to " . BookingStatus::CANCELED->name
            );
        }

        $id = $booking->id ?? throw new \RuntimeException('Booking has no ID');
        $updatedBooking = $booking
            ->withBookingStatus(BookingStatus::CANCELED)
            ->appendLogEntry(new LogEntry(
                eventType: BookingEvent::Rejected,
                actor: $this->currentActorProvider->current(),
                timestamp: $this->clock->now(),
            ));

        $this->repository->updateStatus($id, BookingStatus::CANCELED, $updatedBooking->logEntries);
    }
}
