<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Booking\Application\DTOs\BookingListItem;
use Contexis\Events\Booking\Application\DTOs\BookingListRequest;
use Contexis\Events\Booking\Application\DTOs\BookingListResponse;
use Contexis\Events\Booking\Domain\AttendeeRepository;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Domain\ValueObjects\BookingNotesCollection;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatusCounts;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntryCollection;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\TicketBookings;
use Contexis\Events\Booking\Domain\ValueObjects\TicketBookingsMap;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Payment\Domain\TransactionRepository;
use Contexis\Events\Shared\Application\ValueObjects\Pagination;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Infrastructure\Contracts\Database;
use Contexis\Events\Shared\Infrastructure\Enums\DatabaseOutput;

class DbBookingRepository implements BookingRepository
{
    public function __construct(
        private Database $db,
        private AttendeeRepository $attendeeRepository,
        private TransactionRepository $transactionRepository,
        private BookingHydrator $bookingHydrator,
        private BookingCollectionHydrator $bookingCollectionHydrator,
    ) {
    }

    public function save(Booking $booking): BookingId
    {
        $table = BookingMigration::getTableName();
        $data = [
            'uuid'         => $booking->reference->toString(),
            'event_id'     => $booking->eventId->toInt(),
            'spaces'       => $booking->countAttendees(),
            'email'        => $booking->email->toString(),
            'status'       => $booking->status->value,
            'price_summary'  => wp_json_encode($booking->priceSummary->toArray()),
            'registration' => wp_json_encode($booking->registration->all()),
            'gateway'      => $booking->gateway,
            'coupon_id'    => $booking->coupon?->id?->toInt(),
            'log'          => wp_json_encode(array_map(
                static fn ($entry): array => $entry->toArray(),
                $booking->logEntries->toArray(),
            )),
        ];

        $insertId = $this->db->insert($table, $data);

        if ($insertId === false || $insertId === 0) {
            throw new \RuntimeException('Failed to save booking.');
        }

        return BookingId::from($insertId);
    }

    public function find(BookingId $id): ?Booking
    {
        $table = BookingMigration::getTableName();
        $sql = "SELECT * FROM $table WHERE id = %s";
        $result = $this->db->getRow($this->db->prepare($sql, $id->toInt()), DatabaseOutput::ARRAY_ASSOC);

        if (!$result) {
            return null;
        }

        return $this->bookingHydrator->hydrate($result);
    }

    public function findByReference(string $reference): ?Booking
    {
        $table = BookingMigration::getTableName();
        $sql = "SELECT * FROM $table WHERE uuid = %s";
        $result = $this->db->getRow($this->db->prepare($sql, $reference), DatabaseOutput::ARRAY_ASSOC);

        if (!$result) {
            return null;
        }

        return $this->bookingHydrator->hydrate($result);
    }

	public function findByEventId(EventId $eventId): array
	{
		$table = BookingMigration::getTableName();
		$sql = "SELECT * FROM $table WHERE event_id = %d";
		$results = $this->db->getResults($this->db->prepare($sql, $eventId->toInt()), DatabaseOutput::ARRAY_ASSOC);

		return $this->bookingCollectionHydrator->hydrate($results);
    }

    public function search(BookingListRequest $query): BookingListResponse
    {
        global $wpdb;
        $bookingTable = BookingMigration::getTableName();

        [$whereSql, $params] = $this->buildWhereClause($query);
        [$whereNoStatusSql, $noStatusParams] = $this->buildStatusCountWhereClause($query);

        $allowedOrderBy = ['date', 'status', 'event_id'];
        $orderBy = in_array($query->orderBy, $allowedOrderBy, true) ? $query->orderBy : 'date';
        $order = strtoupper($query->order) === 'ASC' ? 'ASC' : 'DESC';
        $offset = ($query->page - 1) * $query->perPage;

        $mainSql = $this->db->prepare(
            "SELECT b.*, p.post_title AS event_title
            FROM {$bookingTable} b
            LEFT JOIN {$wpdb->posts} p ON p.ID = b.event_id
            {$whereSql}
            ORDER BY b.{$orderBy} {$order}
            LIMIT %d OFFSET %d",
            ...[...$params, $query->perPage, $offset]
        );

        $rows = $this->db->getResults($mainSql);
        $items = array_map([$this, 'mapRowToListItem'], $rows);

        $countSql = $params !== []
            ? $this->db->prepare("SELECT COUNT(*) FROM {$bookingTable} b {$whereSql}", ...$params)
            : "SELECT COUNT(*) FROM {$bookingTable} b {$whereSql}";
        $totalItems = (int) $this->db->getVar($countSql);

        $statusCountSql = $noStatusParams !== []
            ? $this->db->prepare(
                "SELECT b.status, COUNT(*) AS cnt FROM {$bookingTable} b {$whereNoStatusSql} GROUP BY b.status",
                ...$noStatusParams
            )
            : "SELECT b.status, COUNT(*) AS cnt FROM {$bookingTable} b {$whereNoStatusSql} GROUP BY b.status";
        $statusRows = $this->db->getResults($statusCountSql);
        $statusCounts = [];
        foreach ($statusRows as $statusRow) {
            $statusCounts[$this->rowInt($statusRow, 'status')] = $this->rowInt($statusRow, 'cnt');
        }

        $pagination = Pagination::of(
            totalItems: $totalItems,
            currentPage: $query->page,
            perPage: $query->perPage,
        );

        return (BookingListResponse::from(...$items))
            ->withPagination($pagination)
            ->withStatusCounts(new BookingStatusCounts(
                pending: $statusCounts[BookingStatus::PENDING->value] ?? 0,
                approved: $statusCounts[BookingStatus::APPROVED->value] ?? 0,
                canceled: $statusCounts[BookingStatus::CANCELED->value] ?? 0,
                expired: $statusCounts[BookingStatus::EXPIRED->value] ?? 0,
            ));
    }

    public function delete(BookingId $id): void
    {
        $table = BookingMigration::getTableName();
        $this->db->delete($table, ['id' => $id->toInt()]);
    }

    public function update(Booking $booking): void
    {
        $table         = BookingMigration::getTableName();
        $attendeeTable = AttendeeMigration::getTableName();

        $this->db->update($table, [
            'email'        => $booking->email->toString(),
            'registration' => wp_json_encode($booking->registration->all()),
            'gateway'      => $booking->gateway,
            'price_summary' => wp_json_encode($booking->priceSummary->toArray()),
            'notes'        => wp_json_encode($booking->notes->toArray()),
            'log'          => wp_json_encode(array_map(
                static fn ($entry): array => $entry->toArray(),
                $booking->logEntries->toArray(),
            )),
            'spaces'       => $booking->countAttendees(),
        ], ['id' => $booking->id->toInt()]);

        $this->db->delete($attendeeTable, ['booking_id' => $booking->id->toInt()]);

        foreach ($booking->attendees as $attendee) {
            $this->db->insert($attendeeTable, [
                'booking_id' => $booking->id->toInt(),
                'ticket_id'  => $attendee->ticketId->toString(),
                'first_name' => $attendee->name?->firstName,
                'last_name'  => $attendee->name?->lastName,
                'metadata'   => wp_json_encode($attendee->metadata),
            ]);
        }
    }

    public function updateNotes(BookingId $id, BookingNotesCollection $notes): void
    {
        $table = BookingMigration::getTableName();

        $this->db->update($table, [
            'notes' => wp_json_encode($notes->toArray()),
        ], ['id' => $id->toInt()]);
    }

    public function updateStatus(BookingId $id, BookingStatus $status, LogEntryCollection $logEntries): void
    {
        $table = BookingMigration::getTableName();
        $sql = $this->db->prepare(
            "UPDATE {$table} SET status = %d, log = %s WHERE id = %d",
            $status->value,
            wp_json_encode(array_map(
                static fn ($entry): array => $entry->toArray(),
                $logEntries->toArray(),
            )),
            $id->toInt()
        );
        $this->db->query($sql);
    }

    public function getTicketBookingsForEvent(EventId $eventId, array $ticketIds = []): TicketBookingsMap
    {
        $expectedTicketIds = $this->normalizeTicketIds($ticketIds);

        $attendeeTable = AttendeeMigration::getTableName();
        $bookingTable = BookingMigration::getTableName();
        $ticketFilterSql = '';

        if ($expectedTicketIds !== []) {
            $ticketPlaceholders = implode(', ', array_fill(0, count($expectedTicketIds), '%s'));
            $ticketFilterSql = " AND a.ticket_id IN ({$ticketPlaceholders})";
        }

        $sql = $this->db->prepare(
            "SELECT
				a.ticket_id,
				SUM(CASE WHEN b.status = %d THEN 1 ELSE 0 END) as pending,
				SUM(CASE WHEN b.status = %d THEN 1 ELSE 0 END) as approved,
				SUM(CASE WHEN b.status = %d THEN 1 ELSE 0 END) as canceled,
				SUM(CASE WHEN b.status = %d THEN 1 ELSE 0 END) as expired
			FROM {$attendeeTable} a
			JOIN {$bookingTable} b ON a.booking_id = b.id
			WHERE b.event_id = %d
			{$ticketFilterSql}
			GROUP BY a.ticket_id",
            BookingStatus::PENDING->value,
            BookingStatus::APPROVED->value,
            BookingStatus::CANCELED->value,
            BookingStatus::EXPIRED->value,
            $eventId->toInt(),
            ...array_map(static fn (TicketId $ticketId): string => $ticketId->toString(), $expectedTicketIds)
        );

        $rows = $this->db->getResults($sql);

        $result = [];

        if ($ticketIds === []) {
            foreach ($rows as $row) {
                $result[] = new TicketBookings(
                    ticketId: TicketId::from($this->rowString($row, 'ticket_id')),
                    pending: $this->rowInt($row, 'pending'),
                    approved: $this->rowInt($row, 'approved'),
                    canceled: $this->rowInt($row, 'canceled'),
                    expired: $this->rowInt($row, 'expired')
                );
            }
        } else {
            $rowsByTicket = [];
            foreach ($rows as $row) {
                $rowsByTicket[$this->rowString($row, 'ticket_id')] = $row;
            }

            foreach ($ticketIds as $ticketId) {
                $row = $rowsByTicket[$ticketId] ?? null;
                $result[] = new TicketBookings(
                    ticketId: TicketId::from($ticketId),
                    pending: $row !== null ? $this->rowInt($row, 'pending') : 0,
                    approved: $row !== null ? $this->rowInt($row, 'approved') : 0,
                    canceled: $row !== null ? $this->rowInt($row, 'canceled') : 0,
                    expired: $row !== null ? $this->rowInt($row, 'expired') : 0
                );
            }
        }

        return new TicketBookingsMap($result);
    }

    public function getTicketBookingsForEvents(array $eventIds): array
    {
        if ($eventIds === []) {
            return [];
        }

        $eventPlaceholders = implode(', ', array_fill(0, count($eventIds), '%d'));

        $attendeeTable = AttendeeMigration::getTableName();
        $bookingTable = BookingMigration::getTableName();

        $sql = $this->db->prepare(
            "SELECT
				b.event_id,
				a.ticket_id,
				SUM(CASE WHEN b.status = %d THEN 1 ELSE 0 END) as pending,
				SUM(CASE WHEN b.status = %d THEN 1 ELSE 0 END) as approved,
				SUM(CASE WHEN b.status = %d THEN 1 ELSE 0 END) as canceled,
				SUM(CASE WHEN b.status = %d THEN 1 ELSE 0 END) as expired
			FROM {$attendeeTable} a
			JOIN {$bookingTable} b ON a.booking_id = b.id
			WHERE b.event_id IN ({$eventPlaceholders})
			GROUP BY b.event_id, a.ticket_id",
            BookingStatus::PENDING->value,
            BookingStatus::APPROVED->value,
            BookingStatus::CANCELED->value,
            BookingStatus::EXPIRED->value,
            ...array_map(static fn (EventId $eventId): int => $eventId->toInt(), $eventIds)
        );

        $rows = $this->db->getResults($sql);

        $result = [];
        foreach ($eventIds as $eventId) {
            $result[$eventId->toInt()] = [];
        }

        foreach ($rows as $row) {
            $eventKey = $this->rowInt($row, 'event_id');

            $result[$eventKey][] = new TicketBookings(
                ticketId: TicketId::from($this->rowString($row, 'ticket_id')),
                pending: $this->rowInt($row, 'pending'),
                approved: $this->rowInt($row, 'approved'),
                canceled: $this->rowInt($row, 'canceled'),
                expired: $this->rowInt($row, 'expired')
            );
        }

        foreach ($result as $eventKey => $ticketBookings) {
            $result[$eventKey] = new TicketBookingsMap($ticketBookings);
        }

        return $result;
    }

    /** @return array{string, array} */
    private function buildWhereClause(BookingListRequest $query): array
    {
        [$conditions, $params] = $this->buildBaseConditions($query);

        if ($query->status !== null && $query->status !== []) {
            $placeholders = implode(', ', array_fill(0, count($query->status), '%d'));
            $conditions[] = "b.status IN ({$placeholders})";
            $params = array_merge($params, $query->status);
        }

        $whereSql = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$whereSql, $params];
    }

    /** @return array{string, array} */
    private function buildStatusCountWhereClause(BookingListRequest $query): array
    {
        [$conditions, $params] = $this->buildBaseConditions($query);

        $whereSql = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$whereSql, $params];
    }

    /** @return array{array<string>, array} */
    private function buildBaseConditions(BookingListRequest $query): array
    {
        $conditions = [];
        $params = [];

        if ($query->eventId !== null) {
            $conditions[] = 'b.event_id = %d';
            $params[] = $query->eventId->toInt();
        }

        if ($query->gateway !== null && $query->gateway !== '') {
            $conditions[] = 'b.gateway = %s';
            $params[] = $query->gateway;
        }

        if ($query->search !== null && $query->search !== '') {
            global $wpdb; // @phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
            $escaped = $wpdb->esc_like($query->search);
            $conditions[] = '(b.email LIKE %s OR b.registration LIKE %s OR b.uuid LIKE %s)';
            $params[] = '%' . $escaped . '%';
            $params[] = '%' . $escaped . '%';
            $params[] = '%' . $escaped . '%';
        }

        return [$conditions, $params];
    }

    private function mapRowToListItem(object $row): BookingListItem
    {
        $registration = json_decode($row->registration ?? '{}', true) ?? [];
        $firstName = (string) ($registration['first_name'] ?? '');
        $lastName = (string) ($registration['last_name'] ?? '');

        $bookingTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row->date)
            ?: new \DateTimeImmutable($row->date);
		
        return new BookingListItem(
            id: (int) $row->id,
            reference: (string) $row->uuid,
            email: Email::tryFrom((string) $row->email),
            name: PersonName::from($firstName, $lastName),
            eventId: EventId::from((int) $row->event_id),
            eventTitle: (string) ($row->event_title ?? ''),
            status: (int) $row->status,
            priceSummary: $row->price_summary ? PriceSummary::fromArray(json_decode($row->price_summary, true)) : PriceSummary::free(),
            spaces: (int) $row->spaces,
            gateway: isset($row->gateway) ? (string) $row->gateway : null,
            bookingTime: $bookingTime,
        );
    }

    /**
     * @param array<int, mixed> $ticketIds
     * @return TicketId[]
     */
    private function normalizeTicketIds(array $ticketIds): array
    {
        $normalized = [];

        foreach ($ticketIds as $ticketId) {
            if (!is_string($ticketId) || $ticketId === '') {
                continue;
            }

            $resolvedTicketId = TicketId::from($ticketId);
            $normalized[$resolvedTicketId->toString()] = $resolvedTicketId;
        }

        return array_values($normalized);
    }

    private function rowValue(array|object $row, string $key): mixed
    {
        if (is_array($row)) {
            return $row[$key] ?? null;
        }

        return $row->{$key} ?? null;
    }

    private function rowInt(array|object $row, string $key): int
    {
        return (int) $this->rowValue($row, $key);
    }

    private function rowString(array|object $row, string $key): string
    {
        return (string) $this->rowValue($row, $key);
    }

}
