<?php

namespace Contexis\Events\Infrastructure\Persistence;

abstract class AbstractDatabaseRepository {

	protected const TABLE_NAME = '';
	protected const TABLE_DATA = [];
	protected const MODEL_CLASS = '';
	protected const MAPPER_CLASS = '';
	protected const COLLECTION_CLASS = '';

	protected static function get_table_name(): string {
		global $wpdb;
		return $wpdb->prefix . static::TABLE_NAME;
	}

	public static function migrate_table(): void {

        $cols_sql = implode(",\n", static::TABLE_DATA);
        $sql = sprintf(
            "CREATE TABLE IF NOT EXISTS %s (
                %s,
                PRIMARY KEY (id)
            ) DEFAULT CHARSET=utf8mb4;",
            static::get_table_name(),
            $cols_sql
        );

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

	/**
	 * Creates a new record in the database for the given model.
	 * Returns the ID of the newly created record or false on failure.
	 */
	public static function create(object $model): int|bool {
		global $wpdb;
		$table = static::get_table_name();
		$data = $model->to_array();
		
		return $wpdb->insert($table, $data) !== false ? $wpdb->insert_id : false;
	}

	public static function update(object $model): bool {
		global $wpdb;
		$table = static::get_table_name();
		$data = $model->to_array();

		return $wpdb->update($table, $data, ['id' => $model->id]) !== false;
	}

	public static function get_by_id(int $id): ?object {
		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM " . static::get_table_name() . " WHERE id = %d", $id)
		);
		if (!$row) return null;

		$mapper_class = static::MAPPER_CLASS;
		return $mapper_class::map($row);
	}

	public static function find(array $args = []): object {
		global $wpdb;
		[$sql, $params] = static::build_query($args);
		$query = $wpdb->prepare($sql, ...$params);
		$result = $wpdb->get_results($query);

		$mapper_class = static::MAPPER_CLASS;
		$collection_class = static::COLLECTION_CLASS;

		if (!$result) {
			return new $collection_class(); 
		}

		$models = array_map(fn($row) => $mapper_class::map((array)$row), $result);
		
		return new $collection_class($models);
	}

	protected static function build_query(array $args): array {
		return ['SELECT * FROM ' . static::get_table_name(), []];
	}

	
	public static function count(array $args = []): int {
		global $wpdb;

		[$sql, $params] = self::build_query($args);
		$sql = str_replace('SELECT *', 'SELECT SUM(spaces)', $sql); 

		return (int) $wpdb->get_var($wpdb->prepare($sql, ...$params));
	}

	public static function delete(object $model): bool {
		global $wpdb;
        $result = $wpdb->delete(self::get_table_name(), [ 'id' => $model->id ]);
		return $result === false ? false : true;
    }
}