<?php
namespace Contexis\Events\Infrastructure\Persistence;

use Contexis\Events\Domain\Contracts\BookingRepository;
use Contexis\Events\Infrastructure\Persistence\Migration\BookingMigration;

class DbBookingPersistanceRepository implements BookingRepository {

	const TABLE_NAME = 'event_bookings';

	public static function sum_event_spaces(int $event_id): array {
		$table = self::get_table_name();
		global $wpdb;
		$results = $wpdb->get_row(
            $wpdb->prepare("
                SELECT
                    SUM(CASE WHEN status = 1 THEN spaces ELSE 0 END) AS booked,
                    SUM(CASE WHEN status = 0 THEN spaces ELSE 0 END) AS pending,
					SUM(CASE WHEN status = 2 THEN spaces ELSE 0 END) AS rejected,
					SUM(CASE WHEN status = 3 THEN spaces ELSE 0 END) AS canceled,
					SUM(CASE WHEN status = 4 THEN spaces ELSE 0 END) AS expired
                FROM $table
                WHERE event_id = %d
            ", $event_id)
        );

		return [
			'confirmed'     => (int) $results->booked,
			'pendingActive' => (int) $results->pending,
			'rejected'      => (int) $results->rejected,
			'canceled'      => (int) $results->canceled,
			'expired'       => (int) $results->expired
		];
	}

	public static function build_query(array $args = []): array {

		global $wpdb;

		$table = self::get_table_name();
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
				$params[] = '%' . $wpdb->esc_like($value) . '%';
				break;
			}
		}

		if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);

		if (!empty($args['status']) && is_array($args['status'])) {
			$placeholders = implode(',', array_fill(0, count($args['status']), '%d'));
			$sql .= " WHERE status IN ($placeholders)";
			$params = array_merge($params, $args['status']);
		}
		
		if (!empty($args['orderby'])) {
			$valid_fields = ['date', 'status', 'event_id'];
			$orderby = in_array($args['orderby'], $valid_fields, true) ? $args['orderby'] : 'date';
			$order = (strtoupper($args['order'] ?? 'DESC') === 'ASC') ? 'ASC' : 'DESC';
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

	public static function sum_spaces(int $event_id, array $status = []): int {
		global $wpdb;
		$table = BookingMigration::get_table_name();

		$where = ['event_id = %d'];
		$params = [$event_id];
		if (empty($status)) {
			$status = [0, 1, 2, 3, 4, 5, 6, 7, 8];
		}

		$placeholders = implode(',', array_fill(0, count($status), '%d'));
		$where[] = "status IN ($placeholders)";
		$params = array_merge($params, $status);
		
		$sql = "SELECT SUM(spaces) FROM $table WHERE " . implode(' AND ', $where);

		return (int) $wpdb->get_var($wpdb->prepare($sql, ...$params));
	}


    
}
