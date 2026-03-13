<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\Contracts\BookingAction;
use Contexis\Events\Booking\Application\DTOs\BookingActionRequest;
use Contexis\Events\Booking\Domain\AttendeeRepository;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Payment\Domain\TransactionRepository;

final class DeleteBooking implements BookingAction
{
    public function __construct(
        private BookingRepository $repository,
        private AttendeeRepository $attendeeRepository,
        private TransactionRepository $transactionRepository,
    ) {
    }

    public function execute(BookingActionRequest $request): void
    {
        $booking = $this->repository->findByReference($request->reference);

        if ($booking === null) {
            throw new \DomainException("Booking not found: {$request->reference}");
        }

        $id = $booking->id ?? throw new \RuntimeException('Booking has no ID');
        $this->transactionRepository->deleteByBookingId($id);
        $this->attendeeRepository->deleteByBookingId($id);
        $this->repository->delete($id);
    }
}
