<?php

namespace Contexis\Events\Infrastructure\Wordpress;

use Contexis\Events\Infrastructure\Persistence\Migration\BookingMigration;

class Migration {

	private const MIGRATIONS = [
		BookingMigration::class,
	];

	public static function run(): void
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();

        foreach (self::MIGRATIONS as $class) {

            $migration = new $class();

            $table = $migration->get_table_name();
            $cols  = $migration->get_columns_as_string();

            $sql = "CREATE TABLE {$table} (
 				 {$cols},
  				 PRIMARY KEY  (id)
			) {$charset};";

            dbDelta($sql);

        }
    }

	public static function update(): void
	{
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();

		foreach (self::MIGRATIONS as $class) {

			$migration = new $class();

			$table = $migration->get_table_name();
			$cols  = $migration->get_columns_as_string();

			$sql = "UPDATE TABLE {$table} (
 				 {$cols},
  				 PRIMARY KEY  (id)
			) {$charset};";

			dbDelta($sql);

		}
		
	}
}