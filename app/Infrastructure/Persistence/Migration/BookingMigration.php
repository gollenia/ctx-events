<?php

namespace Contexis\Events\Infrastructure\Migration\Migration;

final class BookingMigration implements Migration {

	private string $table_name = 'ctx_event_bookings';

	private array $columns = [
		'id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
		'event_id BIGINT UNSIGNED NOT NULL',
		'spaces TINYINT NOT NULL',
		'user_email VARCHAR(200) NOT NULL',
		'date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
		'status TINYINT NOT NULL DEFAULT 0',
		'full_price DECIMAL(10,2) NOT NULL DEFAULT 0',
		'donation DECIMAL(10,2) NOT NULL DEFAULT 0',
		'registration JSON NULL',
		'attendees JSON NULL',
		'coupon_id BIGINT UNSIGNED NULL',
		'gateway VARCHAR(50) NULL',
		'notes JSON NULL',
		'log JSON NULL',
		'transactions JSON NULL'
	];

	public function get_columns(): array {
		return $this->columns;
	}

	public function get_columns_as_string(): string {
		return implode(",\n  ", $this->columns);
	}

	public function get_table_name(): string {
		global $wpdb;
		return $wpdb->prefix . $this->table_name;
	}
}



	