<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Booking\Domain\AttendeeRepository;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Infrastructure\Mapper\BookingMapper;
use Contexis\Events\Payment\Domain\TransactionRepository;

final class BookingHydrator
{
    public function __construct(
        private AttendeeRepository $attendeeRepository,
        private TransactionRepository $transactionRepository,
    ) {
    }

    public function hydrate(array $row): Booking
    {
        $bookingId = BookingId::from((int) ($row['id'] ?? 0));

        if ($bookingId === null) {
            throw new \RuntimeException('Booking row has no valid ID.');
        }

        $row['attendees'] = $this->attendeeRepository->findByBookingId($bookingId)->toArray();
        $row['transactions'] = $this->transactionRepository->findByBookingId($bookingId)->toArray();

        return BookingMapper::map($row);
    }
}
