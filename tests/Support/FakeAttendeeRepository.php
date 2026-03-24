<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\AttendeeRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;

final class FakeAttendeeRepository implements AttendeeRepository
{
    /** @var array<int, AttendeeCollection> */
    private array $attendeesByBookingId = [];

    public ?BookingId $lastFindArg = null;

    public static function empty(): self
    {
        return new self();
    }

    public static function withBookingAttendees(BookingId $bookingId, AttendeeCollection $attendees): self
    {
        $repository = new self();
        $repository->attendeesByBookingId[$bookingId->toInt()] = $attendees;

        return $repository;
    }

    public function saveAll(AttendeeCollection $attendees, BookingId $bookingId): void
    {
        $this->attendeesByBookingId[$bookingId->toInt()] = $attendees;
    }

    public function findByBookingId(BookingId $bookingId): AttendeeCollection
    {
        $this->lastFindArg = $bookingId;

        return $this->attendeesByBookingId[$bookingId->toInt()] ?? AttendeeCollection::empty();
    }

    /**
     * @param BookingId[] $bookingIds
     * @return array<int, AttendeeCollection>
     */
    public function findByBookingIds(array $bookingIds): array
    {
        $result = [];

        foreach ($bookingIds as $bookingId) {
            $result[$bookingId->toInt()] = $this->findByBookingId($bookingId);
        }

        return $result;
    }

    public function deleteByBookingId(BookingId $bookingId): void
    {
        unset($this->attendeesByBookingId[$bookingId->toInt()]);
    }
}
