<?php
namespace Contexis\Events\Repositories;

use Contexis\Events\Models\Booking;
use Contexis\Events\Collections\BookingCollection;
use Contexis\Events\Collections\NoteCollection;
use Contexis\Events\Collections\TransactionCollection;
use Contexis\Events\Models\BookingStatus;
use wpdb;

class BookingRepository {

	const TABLE_NAME = 'event_bookings';

	public static function migrate_table() {
		$sql = "CREATE TABLE IF NOT EXISTS " . self::table() . " (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			event_id BIGINT UNSIGNED NOT NULL,
			spaces TINYINT UNSIGNED NOT NULL,
			user_email VARCHAR(254) NOT NULL,
			date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			status TINYINT UNSIGNED NOT NULL DEFAULT 1,
			full_price DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0.0000,
			donation DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
			registration JSON NULL,
			attendees JSON NULL,
			coupon_id BIGINT UNSIGNED DEFAULT 0,
			gateway VARCHAR(50) NULL,
			consent TINYINT(1) DEFAULT 0,
			notes JSON NULL,
			log JSON NULL,
			transactions JSON NULL,
			PRIMARY KEY (id),
			INDEX idx_event_id (event_id),
			INDEX idx_status (status)
		) DEFAULT CHARSET=utf8 ;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . self::TABLE_NAME;
	}

    public static function update(Booking $booking): bool {
		global $wpdb;
        $table = self::table();

        $data = $booking->to_array();

        return $wpdb->update($table, $data, ['id' => $booking->id]) !== false;
    }


	public static function create(Booking $booking): int|bool {
		global $wpdb;
		$table = self::table();

		$data = $booking->to_array();
		if($booking->coupon_id !== 0) {
			$coupon = \Contexis\Events\Models\Coupon::get_by_id($booking->coupon_id);
			$coupon->increment_used();
		}
		
		return $wpdb->insert($table, $data) !== false ? $wpdb->insert_id : false;
	}

    public static function get_by_id(int $id): ?Booking {
		global $wpdb;
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM " . self::table() . " WHERE id = %d", $id)
        );

        if (!$row) return null;

        return self::from_db_row($row); // Factory-Methode
    }

	public static function find(array $args = []): BookingCollection {
		global $wpdb;

		[$sql, $params] = self::build_query($args);
		
		$query = $wpdb->prepare($sql, ...$params);
	    $result = $wpdb->get_results($query);
		if (!$result) {
			return new BookingCollection(); // Return empty collection if no results
		}
		
		$bookings = array_map(function($row) {
			return self::from_db_row($row);
		}, $result);

		return BookingCollection::from_bookings($bookings);
	}

	public static function count(array $args = []): int {
		global $wpdb;

		[$sql, $params] = self::build_query($args);
		$sql = str_replace('SELECT *', 'SELECT SUM(spaces)', $sql); // Modify query to sum spaces

		return (int) $wpdb->get_var($wpdb->prepare($sql, ...$params));
	}

	public static function sum_event_spaces(int $event_id): array {
		$table = self::table();
		global $wpdb;
		$results = $wpdb->get_row(
            $wpdb->prepare("
                SELECT
                    SUM(CASE WHEN status = 1 THEN spaces ELSE 0 END) AS booked,
                    SUM(CASE WHEN status IN (0,4) THEN spaces ELSE 0 END) AS pending,
					SUM(CASE WHEN status = 2 THEN spaces ELSE 0 END) AS rejected,
					SUM(CASE WHEN status = 3 THEN spaces ELSE 0 END) AS canceled
                FROM $table
                WHERE event_id = %d
            ", $event_id)
        );

		return [
			'booked' => (int) $results->booked,
			'pending' => (int) $results->pending,
			'rejected' => (int) $results->rejected,
			'canceled' => (int) $results->canceled
		];
	}

	public static function build_query(array $args = []): array {

		global $wpdb;

		$table = self::table();
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

	private static function from_db_row($row): Booking {
		$booking = new Booking();
		$booking->id = (int) $row->id;
		$booking->event_id = (int) $row->event_id;
		$booking->spaces = (int) $row->spaces;
		$booking->user_email = $row->user_email;
		$booking->date = new \DateTime($row->date);
		$booking->status = BookingStatus::from((int) $row->status);
		$booking->full_price = (float) $row->full_price;
		$booking->donation = (float) $row->donation;
		$booking->registration = json_decode($row->registration, true) ?: [];
		$booking->attendees = json_decode($row->attendees, true) ?: [];
		$booking->coupon_id = (int) $row->coupon_id ?? 0;
		$booking->gateway = $row->gateway;
		$booking->consent = (int) $row->consent === 1;
		$booking->notes = NoteCollection::from_array(json_decode($row->notes, true) ?: []);
		$booking->transactions = TransactionCollection::from_array(json_decode($row->transactions, true) ?: []);
		$booking->log = NoteCollection::from_array(json_decode($row->log, true) ?: []);
		return $booking;
	}

	public static function sum_spaces(int $event_id, array $status = []): int {
		global $wpdb;
		$table = self::table();

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


    public static function delete(Booking $booking): bool {
		global $wpdb;
        $result = $wpdb->delete(self::table(), [ 'id' => $booking->id ]);
		return $result === false ? false : true;
    }
}
