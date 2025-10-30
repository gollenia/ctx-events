<?php

namespace Contexis\Events\Infrastructure\Migration\Migration;

final class AttendeeMigration implements Migration {

	private string $table_name = 'attendees';

	private array $columns = [
		'id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
		'ticket_id VARCHAR(100) NOT NULL',
		'booking_id BIGINT UNSIGNED NOT NULL',
		'first_name VARCHAR(100) NOT NULL',
		'last_name VARCHAR(100) NOT NULL',
		'email VARCHAR(200) NOT NULL',
		'metadata JSON NULL'
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