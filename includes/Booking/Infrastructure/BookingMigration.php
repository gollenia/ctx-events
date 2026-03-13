<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Contracts\Migration;

final class BookingMigration implements Migration
{
    private const TABLE_NAME = 'ctx_event_bookings';

	public const ID = 'id';
	public const UUID = 'uuid';
	public const EVENT_ID = 'event_id';
	public const EMAIL = 'email';
	public const DATE = 'date';
	public const STATUS = 'status';
	public const PRICE_SUMMARY = 'price_summary';
	public const CURRENCY = 'currency';	
	public const REGISTRATION = 'registration';
	public const COUPON_ID = 'coupon_id';
	public const GATEWAY = 'gateway';
	public const NOTES = 'notes';
	public const LOG = 'log';

    private array $columns = [
        self::ID . ' BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
		self::UUID . ' VARCHAR(191) NOT NULL',
        self::EVENT_ID . ' BIGINT UNSIGNED NOT NULL',
        self::EMAIL . ' VARCHAR(191) NOT NULL',
        self::DATE . ' TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        self::STATUS . ' TINYINT NOT NULL DEFAULT 1',
        self::PRICE_SUMMARY . ' JSON NULL',
		self::CURRENCY . ' VARCHAR(10) NOT NULL DEFAULT ""',
        self::REGISTRATION . ' JSON NULL',
        self::COUPON_ID . ' BIGINT UNSIGNED NULL',
        self::GATEWAY . ' VARCHAR(50) NULL',
        self::NOTES . ' JSON NULL',
        self::LOG . ' JSON NULL',
        'PRIMARY KEY  (' . self::ID . ')',
		'UNIQUE KEY (' . self::UUID . ')',
		'INDEX (' . self::EVENT_ID . ')',
		'INDEX (' . self::STATUS . ')',
		'INDEX (' . self::EMAIL . ')',
		'INDEX (' . self::COUPON_ID . ')'
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
