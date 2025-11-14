<?php

namespace Contexis\Events\Platform\Wordpress;

use Contexis\Events\Booking\Infrastructure\AttendeeMigration;
use Contexis\Events\Booking\Infrastructure\BookingMigration;
use Contexis\Events\Payment\Infrastructure\TransactionMigration;

final class Migration
{
    private const VERSION = '1.0.2';

    private const MIGRATIONS = [
        BookingMigration::class,
        TransactionMigration::class,
        AttendeeMigration::class
    ];

    public static function migrate(): void
    {
        $current = get_option('ctx_events_db_version', '0');
        if (version_compare($current, self::VERSION, '<')) {
            self::run();
            update_option('ctx_events_db_version', self::VERSION, false);
        }
    }

    private static function run(): void
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();

        foreach (self::MIGRATIONS as $class) {
            $migration = new $class();

            $table = $migration->getTableName();
            $cols  = $migration->getColumnsAsString();

            $sql = "CREATE TABLE `{$table}` (
 				 {$cols}
			) {$charset};";

            dbDelta($sql);
        }
    }
}
