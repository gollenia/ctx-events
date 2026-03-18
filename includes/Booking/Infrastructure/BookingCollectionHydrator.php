<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Booking\Domain\AttendeeRepository;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Infrastructure\Mapper\BookingMapper;
use Contexis\Events\Payment\Domain\TransactionRepository;

final class BookingCollectionHydrator
{
    public function __construct(
        private AttendeeRepository $attendeeRepository,
        private TransactionRepository $transactionRepository,
    ) {
    }

    /** @param array<int, array<string, mixed>> $rows @return Booking[] */
    public function hydrate(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $bookingIds = [];
        foreach ($rows as $row) {
            $bookingId = BookingId::from((int) ($row['id'] ?? 0));
            if ($bookingId !== null) {
                $bookingIds[] = $bookingId;
            }
        }

        $attendeesByBookingId = $this->attendeeRepository->findByBookingIds($bookingIds);
        $transactionsByBookingId = $this->transactionRepository->findByBookingIds($bookingIds);
        $bookings = [];

        foreach ($rows as $row) {
            $bookingId = BookingId::from((int) ($row['id'] ?? 0));

            if ($bookingId === null) {
                continue;
            }

            $row['attendees'] = ($attendeesByBookingId[$bookingId->toInt()] ?? null)?->toArray() ?? [];
            $row['transactions'] = ($transactionsByBookingId[$bookingId->toInt()] ?? null)?->toArray() ?? [];
            $bookings[] = BookingMapper::map($row);
        }

        return $bookings;
    }
}
