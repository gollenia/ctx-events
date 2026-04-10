<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain;

use Contexis\Events\Booking\Domain\ValueObjects\BookingId;

interface AttendeeRepository
{
    public function saveAll(AttendeeCollection $attendees, BookingId $bookingId): void;

    public function findByBookingId(BookingId $bookingId): AttendeeCollection;

    /** 
	 * @param BookingId[] $bookingIds 
	 * @return array<int, AttendeeCollection> 
	 **/
    public function findByBookingIds(array $bookingIds): array;

    public function deleteByBookingId(BookingId $bookingId): void;
}
