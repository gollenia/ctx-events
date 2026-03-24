<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\Services;

use Contexis\Events\Booking\Domain\AttendeeRepository;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Communication\Application\DTOs\TriggeredEmailContext;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Payment\Domain\TransactionRepository;

final readonly class LoadBookingEmailContext
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private EventRepository $eventRepository,
        private AttendeeRepository $attendeeRepository,
        private TransactionRepository $transactionRepository,
    ) {
    }

    public function load(BookingId $bookingId): ?TriggeredEmailContext
    {
        $booking = $this->bookingRepository->find($bookingId);

        if ($booking === null) {
            return null;
        }

        $event = $this->eventRepository->find($booking->eventId);

        if ($event === null) {
            return null;
        }

        return new TriggeredEmailContext(
            booking: $booking,
            event: $event,
            attendees: $this->attendeeRepository->findByBookingId($bookingId),
            transactions: $this->transactionRepository->findByBookingId($bookingId),
        );
    }
}
