<?php
declare(strict_types=1);

namespace Contexis\Events\Platform\Wordpress;

use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;

final class DatabaseMigration implements Registrar
{

	private const VERSION = '1.0.2';

	/*
	 * @var Migration[]
	 */
    public function __construct(
		private readonly iterable $migrations,
	)
    {
    }

    public function hook(): void
    {
        $this->migrate();
    }

    public function all(): array
    {
        return $this->migrations;
    }

	public function migrate(): void
    {
		if ($this->tablesExist() && $this->versionIsUpToDate()) {
			return;
		}

		$this->runDbDelta();
        update_option('ctx_events_db_version', self::VERSION, false);
    }

	private function tablesExist(): bool
	{
		global $wpdb;
		foreach ($this->migrations as $migration) {
			$table = $migration::getTableName();
			if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
				return false;
			}
		}

		return true;
	}

	private function versionIsUpToDate(): bool
	{
		$current = get_option('ctx_events_db_version', '0');

		return version_compare($current, self::VERSION, '>=');
	}

    private function runDbDelta(): void
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();

        foreach ($this->migrations as $migration) {
            $table = $migration::getTableName();
            $cols  = $migration->getColumnsAsString();

            $sql = "CREATE TABLE `{$table}` (
 				 {$cols}
			) {$charset};";

            dbDelta($sql);
        }
    }
}
