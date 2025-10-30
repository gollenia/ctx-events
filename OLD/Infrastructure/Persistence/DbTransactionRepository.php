<?php

namespace Contexis\Events\Repositories;
use Contexis\Events\Models\Transaction;
use Contexis\Events\Collections\TransactionCollection;

class TransactionRepository extends BaseRepository {

	const MODEL_CLASS = Transaction::class;
	const COLLECTION_CLASS = TransactionCollection::class;
	const MAPPER_CLASS = \Contexis\Events\Mappers\TransactionMapper::class;

	const TABLE_NAME = 'event_transactions';
	const TABLE_DATA = [
		'id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
		'gateway_id VARCHAR(200)',
		'booking_id BIGINT UNSIGNED NOT NULL',
		'amount DECIMAL(10,2) NOT NULL',
		'gateway VARCHAR(50) NOT NULL',
		'checkout_url VARCHAR(255) NULL',
		'status VARCHAR(50) NOT NULL',
		'created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
		'expires_at TIMESTAMP NULL DEFAULT NULL',
		'PRIMARY KEY (id)'
	];

	public static function build_query(array $args = []): array {		

		$table = self::get_table_name();
		$sql = "SELECT * FROM $table";
		$where = [];
		$params = [];

		if (isset($args['id'])) {
			$where[] = 'id = %d';
			$params[] = $args['id'];
		}

		if (isset($args['booking_id'])) {
			$where[] = 'booking_id = %d';
			$params[] = $args['booking_id'];
		}

		if (isset($args['vendor_id'])) {
			$where[] = 'vendor_id = %s';
			$params[] = $args['vendor_id'];
		}

		if (isset($args['status'])) {
			if (is_array($args['status']) && !empty($args['status'])) {
				$placeholders = implode(',', array_fill(0, count($args['status']), '%s'));
				$where[] = "status IN ($placeholders)";
				$params = array_merge($params, $args['status']);
			} elseif (is_string($args['status'])) {
				$where[] = 'status = %s';
				$params[] = $args['status'];
			}
		}

		if (!empty($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}

		if (isset($args['order_by']) && in_array($args['order_by'], ['id', 'created_at', 'amount'], true)) {
			$order = (isset($args['order']) && strtoupper($args['order']) === 'ASC') ? 'ASC' : 'DESC';
			$sql .= " ORDER BY {$args['order_by']} $order";
		} else {
			$sql .= " ORDER BY created_at DESC";
		}

		if (isset($args['limit']) && is_int($args['limit']) && $args['limit'] > 0) {
			$sql .= " LIMIT %d";
			$params[] = $args['limit'];
			if (isset($args['offset']) && is_int($args['offset']) && $args['offset'] >= 0) {
				$sql .= " OFFSET %d";
				$params[] = $args['offset'];
			}
		}

		return [$sql, $params];
	}

}