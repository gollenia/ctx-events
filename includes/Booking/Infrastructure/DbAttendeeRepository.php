<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\AttendeeRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Infrastructure\Mapper\AttendeeMapper;
use Contexis\Events\Shared\Infrastructure\Contracts\Database;
use Contexis\Events\Shared\Infrastructure\Enums\DatabaseOutput;

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

        if (($attendee->metadata['first_name'] ?? null) !== null) {
            $row['first_name'] = $attendee->metadata['first_name'];
        }
        if (($attendee->metadata['last_name'] ?? null) !== null) {
            $row['last_name'] = $attendee->metadata['last_name'];
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

    public function findByBookingId(BookingId $bookingId): AttendeeCollection
    {
        $table = AttendeeMigration::getTableName();
        $sql = "SELECT * FROM $table WHERE booking_id = %d";
        $rows = $this->db->getResults($this->db->prepare($sql, $bookingId->toInt()), DatabaseOutput::ARRAY_ASSOC);

        return AttendeeCollection::from(...array_map(AttendeeMapper::map(...), $rows));
    }

    public function findByBookingIds(array $bookingIds): array
    {
        if ($bookingIds === []) {
            return [];
        }

        $ids = array_values(array_unique(array_map(
            static fn(BookingId $bookingId): int => $bookingId->toInt(),
            $bookingIds
        )));

        $placeholders = implode(', ', array_fill(0, count($ids), '%d'));
        $table = AttendeeMigration::getTableName();
        $sql = $this->db->prepare(
            "SELECT * FROM $table WHERE booking_id IN ($placeholders) ORDER BY booking_id ASC",
            ...$ids
        );
        $rows = $this->db->getResults($sql, DatabaseOutput::ARRAY_ASSOC);

        $grouped = [];
        foreach ($rows as $row) {
            $bookingId = (int) ($row['booking_id'] ?? 0);
            $grouped[$bookingId][] = AttendeeMapper::map($row);
        }

        $result = [];
        foreach ($ids as $id) {
            $result[$id] = AttendeeCollection::from(...($grouped[$id] ?? []));
        }

        return $result;
    }
}
