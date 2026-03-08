<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;

final class UpdateBookingStatus
{
    public function __construct(
        private BookingRepository $repository,
    ) {
    }

    public function execute(string $reference, string $newStatusSlug): void
    {
        $booking = $this->repository->findByReference($reference);

        if ($booking === null) {
            throw new \DomainException("Booking not found: {$reference}");
        }

        $newStatus = match ($newStatusSlug) {
            'approved' => BookingStatus::APPROVED,
            'canceled' => BookingStatus::CANCELED,
            'pending'  => BookingStatus::PENDING,
            'deleted'  => BookingStatus::DELETED,
            default    => throw new \DomainException("Invalid status: {$newStatusSlug}"),
        };

        if (!$booking->status->canTransitionTo($newStatus)) {
            throw new \DomainException(
                "Cannot transition from {$booking->status->name} to {$newStatus->name}"
            );
        }

        $id = $booking->id ?? throw new \RuntimeException('Booking has no ID');

        $this->repository->updateStatus($id, $newStatus);
    }
}
