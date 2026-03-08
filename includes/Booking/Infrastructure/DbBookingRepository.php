<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Booking\Application\DTOs\BookingListItem;
use Contexis\Events\Booking\Application\DTOs\BookingListRequest;
use Contexis\Events\Booking\Application\DTOs\BookingListResponse;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\TicketBookings;
use Contexis\Events\Booking\Domain\ValueObjects\TicketBookingsMap;
use Contexis\Events\Booking\Infrastructure\Mapper\BookingMapper;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Payment\Infrastructure\TransactionMigration;
use Contexis\Events\Shared\Application\ValueObjects\Pagination;
use Contexis\Events\Shared\Infrastructure\Contracts\Database;

class DbBookingRepository implements BookingRepository
{
    public function __construct(
        private Database $db
    ) {
    }

    public function save(Booking $booking): BookingId
    {
        $table = BookingMigration::getTableName();
        $data = [
            'uuid'         => $booking->reference->toString(),
            'event_id'     => $booking->eventId->toInt(),
            'email'        => $booking->email->toString(),
            'status'       => $booking->status->value,
            'final_price'  => $booking->priceSummary->finalPrice->amountCents,
            'donation'     => $booking->priceSummary->donationAmount->amountCents,
            'registration' => wp_json_encode($booking->registration->all()),
            'gateway'      => $booking->gateway,
            'coupon_id'    => $booking->coupon?->id?->toInt(),
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
        $result = $this->db->getRow($this->db->prepare($sql, $id->toInt()));

        if (!$result) {
            return null;
        }

        $attendees = $this->getBookingAttendees($id);
        $transactions = $this->getBookingTransactions($id);

        $result['attendees'] = $attendees;
        $result['transactions'] = $transactions;
        return BookingMapper::map($result);
    }

    public function findByReference(string $reference): ?Booking
    {
        $table = BookingMigration::getTableName();
        $sql = "SELECT * FROM $table WHERE uuid = %s";
        $result = $this->db->getRow($this->db->prepare($sql, $reference));

        if (!$result) {
            return null;
        }

        $attendees = $this->getBookingAttendees(BookingId::from($result['id']));
        $transactions = $this->getBookingTransactions(BookingId::from($result['id']));

        $result['attendees'] = $attendees;
        $result['transactions'] = $transactions;
        return BookingMapper::map($result);
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

        if ($items !== []) {
            $bookingIds = array_map(static fn (BookingListItem $item): int => $item->id, $items);
            $items = $this->enrichWithTicketBreakdown($items, $bookingIds);
        }

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
            $statusCounts[(int) $statusRow->status] = (int) $statusRow->cnt;
        }

        $pagination = Pagination::of(
            totalItems: $totalItems,
            currentPage: $query->page,
            perPage: $query->perPage,
        );

        return (new BookingListResponse(...$items))
            ->withPagination($pagination)
            ->withStatusCounts($statusCounts);
    }

    public function delete(BookingId $id): void
    {
        $table = BookingMigration::getTableName();
        $this->db->delete($table, ['id' => $id->toInt()]);
    }

    public function updateStatus(BookingId $id, BookingStatus $status): void
    {
        $table = BookingMigration::getTableName();
        $sql = $this->db->prepare(
            "UPDATE {$table} SET status = %d WHERE id = %d",
            $status->value,
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
                    ticketId: TicketId::from($row->ticket_id),
                    pending: (int) $row->pending,
                    approved: (int) $row->approved,
                    canceled: (int) $row->canceled,
                    expired: (int) $row->expired
                );
            }
        } else {
            $rowsByTicket = array_column($rows, null, 'ticket_id');
            foreach ($ticketIds as $ticketId) {
                $row = $rowsByTicket[$ticketId] ?? null;
                $result[] = new TicketBookings(
                    ticketId: TicketId::from($ticketId),
                    pending: $row !== null ? (int) $row->pending : 0,
                    approved: $row !== null ? (int) $row->approved : 0,
                    canceled: $row !== null ? (int) $row->canceled : 0,
                    expired: $row !== null ? (int) $row->expired : 0
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
            $eventKey = (int) $row->event_id;

            $result[$eventKey][] = new TicketBookings(
                ticketId: TicketId::from($row->ticket_id),
                pending: (int) $row->pending,
                approved: (int) $row->approved,
                canceled: (int) $row->canceled,
                expired: (int) $row->expired
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
            $conditions[] = '(b.email LIKE %s OR b.registration LIKE %s)';
            $params[] = '%' . $escaped . '%';
            $params[] = '%' . $escaped . '%';
        }

        return [$conditions, $params];
    }

    /**
     * @param BookingListItem[] $items
     * @param int[]             $bookingIds
     * @return BookingListItem[]
     */
    private function enrichWithTicketBreakdown(array $items, array $bookingIds): array
    {
        $attendeeTable = AttendeeMigration::getTableName();
        $placeholders = implode(', ', array_fill(0, count($bookingIds), '%d'));

        $sql = $this->db->prepare(
            "SELECT booking_id, ticket_id, COUNT(*) AS count
            FROM {$attendeeTable}
            WHERE booking_id IN ({$placeholders})
            GROUP BY booking_id, ticket_id",
            ...$bookingIds
        );

        $rows = $this->db->getResults($sql);

        $breakdownByBooking = [];
        foreach ($rows as $row) {
            $breakdownByBooking[(int) $row->booking_id][(string) $row->ticket_id] = (int) $row->count;
        }

        return array_map(
            static fn (BookingListItem $item): BookingListItem =>
                isset($breakdownByBooking[$item->id])
                    ? $item->withTicketBreakdown($breakdownByBooking[$item->id])
                    : $item,
            $items
        );
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
            email: (string) $row->email,
            firstName: $firstName,
            lastName: $lastName,
            eventId: (int) $row->event_id,
            eventTitle: (string) ($row->event_title ?? ''),
            status: (int) $row->status,
            finalPrice: (int) $row->final_price,
            donationAmount: (int) $row->donation,
            gateway: isset($row->gateway) ? (string) $row->gateway : null,
            bookingTime: $bookingTime,
        );
    }

    /**
     * @param string[] $ticketIds
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
            if ($resolvedTicketId === null) {
                continue;
            }

            $normalized[$resolvedTicketId->toString()] = $resolvedTicketId;
        }

        return array_values($normalized);
    }

    private function getBookingAttendees(BookingId $id): array
    {
        $table = AttendeeMigration::getTableName();
        $sql = "SELECT * FROM $table WHERE booking_id = %d";
        $result = $this->db->getResults($this->db->prepare($sql, $id->toInt()));
        return $result;
    }

    private function getBookingTransactions(BookingId $id): array
    {
        $table = TransactionMigration::getTableName();
        $sql = "SELECT * FROM $table WHERE booking_id = %d";
        $result = $this->db->getResults($this->db->prepare($sql, $id->toInt()));
        return $result;
    }
}
