<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\AttendeeRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Shared\Infrastructure\Contracts\Database;

final class DbAttendeeRepository implements AttendeeRepository
{
    public function __construct(private Database $db) {}

    public function saveAll(AttendeeCollection $attendees, BookingId $bookingId): void
    {
        $table = AttendeeMigration::getTableName();

        foreach ($attendees as $attendee) {
            $this->saveOne($attendee, $bookingId, $table);
        }
    }

    private function saveOne(Attendee $attendee, BookingId $bookingId, string $table): void
    {
        $metadata = $attendee->metadata;
        if ($attendee->birthDate !== null) {
            $metadata['birth_date'] = $attendee->birthDate->format('Y-m-d');
        }

        $row = [
            'booking_id' => $bookingId->toInt(),
            'ticket_id'  => $attendee->ticketId->toString(),
            'metadata'   => wp_json_encode($metadata),
        ];

        if ($attendee->firstName !== null) {
            $row['first_name'] = $attendee->firstName;
        }
        if ($attendee->lastName !== null) {
            $row['last_name'] = $attendee->lastName;
        }

        $result = $this->db->insert($table, $row);

        if ($result === false) {
            throw new \RuntimeException('Failed to save attendee.');
        }
    }

    public function deleteByBookingId(BookingId $bookingId): void
    {
        $table = AttendeeMigration::getTableName();
        $this->db->delete($table, ['booking_id' => $bookingId->toInt()]);
    }
}
