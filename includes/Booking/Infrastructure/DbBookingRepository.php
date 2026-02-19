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

class DbBookingPersistanceRepository implements BookingRepository
{
	public function __construct(
		private Database $db
	)
	{
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

	public function getBookingsForEvent(EventId $eventId): TicketBookingsMap
	{

		$attendeeTable = AttendeeMigration::getTableName();
		$bookingTable = BookingMigration::getTableName();

		$sql = $this->db->prepare(
			"SELECT 
				a.ticket_id,
				SUM(CASE WHEN b.status = %d THEN 1 ELSE 0 END) as pending,
				SUM(CASE WHEN b.status = %d THEN 1 ELSE 0 END) as approved,
				SUM(CASE WHEN b.status = %d THEN 1 ELSE 0 END) as cancelled,
				SUM(CASE WHEN b.status = %d THEN 1 ELSE 0 END) as expired
			FROM {$attendeeTable} a
			JOIN {$bookingTable} b ON a.booking_id = b.id
			WHERE b.event_id = %d 
			GROUP BY a.ticket_id",
			BookingStatus::PENDING->value,
			BookingStatus::APPROVED->value,
			BookingStatus::CANCELED->value,
			BookingStatus::EXPIRED->value,
			$eventId->toInt()
		);

    	$rows = $this->db->getResults($sql);

		$statsObjects = [];
		foreach ($rows as $row) {
			$statsObjects[] = new TicketBookings(
				TicketId::from($row->ticket_id),
				(int)$row->pending,
				(int)$row->approved,
				(int)$row->cancelled,
				(int)$row->expired
			);
		}

		return new TicketBookingsMap($statsObjects);
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
