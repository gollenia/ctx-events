<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\TicketBookings;
use Contexis\Events\Booking\Domain\ValueObjects\TicketBookingsMap;
use Contexis\Events\Booking\Domain\ValueObjects\TicketSalesCount;
use Contexis\Events\Booking\Domain\ValueObjects\TicketSalesStats;
use Contexis\Events\Booking\Infrastructure\BookingMigration;
use Contexis\Events\Booking\Infrastructure\Mapper\BookingMapper;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Payment\Infrastructure\TransactionMigration;
use Contexis\Events\Shared\Infrastructure\Contracts\Database;
use Contexis\Events\Shared\Infrastructure\ValueObjects\Order;

class DbBookingRepository implements BookingRepository
{
	public function __construct(
		private Database $db
	)
	{
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

    public function buildQuery(array $args = []): array
    {

        $table = BookingMigration::getTableName();
        $sql = "SELECT * FROM $table";
        $where = [];
        $params = [];

        foreach ($args as $key => $value) {
            switch ($key) {
                case 'event_id':
                case 'coupon_id':
                    $where[] = "$key = %d";
                    $params[] = (int)$value;
                    break;

                case 'user_email':
                case 'gateway':
                    $where[] = "$key = %s";
                    $params[] = $value;
                    break;

                case 'search':
                    $where[] = "registration LIKE %s";
                    $params[] = '%' . $this->db->db->esc_like($value) . '%';
                    break;
            }
        }

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        if (!empty($args['status']) && is_array($args['status'])) {
            $placeholders = implode(',', array_fill(0, count($args['status']), '%d'));
            $sql .= " WHERE status IN ($placeholders)";
            $params = array_merge($params, $args['status']);
        }

        if (!empty($args['orderby'])) {
            $valid_fields = ['date', 'status', 'event_id'];
            $orderby = in_array($args['orderby'], $valid_fields, true) ? $args['orderby'] : 'date';
            $order = (strtoupper($args['order'] ?? Order::ASC->value) === Order::ASC->value) ? Order::ASC->value : Order::DESC->value;
            $sql .= " ORDER BY $orderby $order";
        }

        if (!empty($args['limit']) && is_numeric($args['limit'])) {
            $sql .= " LIMIT %d";
            $params[] = (int)$args['limit'];

            if (!empty($args['offset']) && is_numeric($args['offset'])) {
                $sql .= " OFFSET %d";
                $params[] = (int)$args['offset'];
            }
        }

        return [$sql, $params];
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
