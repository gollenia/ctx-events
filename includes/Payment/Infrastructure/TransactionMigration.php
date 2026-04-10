<?php
declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Contracts\Migration;

final class TransactionMigration implements Migration
{
    private const TABLE_NAME = 'ctx_event_transactions';

	public const ID = 'id';
	public const EXTERNAL_ID = 'external_id';
	public const BOOKING_ID = 'booking_id';
	public const AMOUNT = 'amount';
	public const CURRENCY = 'currency';
	public const GATEWAY = 'gateway';
	public const STATUS = 'status';
	public const TRANSACTION_DATE = 'transaction_date';
	public const EXPIRES_AT = 'expires_at';
	public const CREATED_AT = 'created_at';
	public const DETAILS = 'details';

	/** @var non-empty-list<non-empty-string> */
    private array $columns = [
        self::ID . ' BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
        self::EXTERNAL_ID . ' VARCHAR(100) NULL',
        self::BOOKING_ID . ' BIGINT UNSIGNED NOT NULL',
        self::AMOUNT . ' BIGINT NOT NULL DEFAULT 0',
        self::CURRENCY . ' VARCHAR(3) NOT NULL',
        self::GATEWAY . ' VARCHAR(50) NOT NULL',
        self::STATUS . ' TINYINT NOT NULL DEFAULT 0',
        self::TRANSACTION_DATE . ' TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        self::EXPIRES_AT . ' TIMESTAMP NULL',
        self::CREATED_AT . ' TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        self::DETAILS . ' JSON NULL',
        'PRIMARY KEY  (' . self::ID . ')',
		'UNIQUE KEY (' . self::EXTERNAL_ID . ')',
		'INDEX (' . self::BOOKING_ID . ')',
		'INDEX (' . self::STATUS . ')',
		'INDEX (' . self::EXPIRES_AT . ')'
    ];

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getColumnsAsString(): string
    {
        return implode(",\n  ", $this->columns);
    }

    public static function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_NAME;
    }
}
