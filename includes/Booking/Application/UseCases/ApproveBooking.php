<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;

final class ApproveBooking
{
    public function __construct(
        private BookingRepository $repository,
    ) {
    }

    public function execute(string $reference): void
    {
        $booking = $this->repository->findByReference($reference);

        if ($booking === null) {
            throw new \DomainException("Booking not found: {$reference}");
        }

        if (!$booking->status->canTransitionTo(BookingStatus::APPROVED)) {
            throw new \DomainException(
                "Cannot transition from {$booking->status->name} to " . BookingStatus::APPROVED->name
            );
        }

        $id = $booking->id ?? throw new \RuntimeException('Booking has no ID');

        $this->repository->updateStatus($id, BookingStatus::APPROVED);
    }
}
