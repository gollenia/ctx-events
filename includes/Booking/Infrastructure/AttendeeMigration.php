<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Contracts\Migration;

final class AttendeeMigration implements Migration
{
    private const TABLE_NAME = 'ctx_event_attendees';

	public const ID = 'id';
	public const TICKET_ID = 'ticket_id';
	public const TICKET_PRICE = 'ticket_price';
	public const BOOKING_ID = 'booking_id';
	public const FIRST_NAME = 'first_name';
	public const LAST_NAME = 'last_name';
	public const STATUS = 'status';
	public const METADATA = 'metadata';
	public const CHECKIN_AT = 'checkin_at';

	/** @var array<string> */
    private array $columns = [
        self::ID . ' BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
        self::TICKET_ID . ' VARCHAR(100) NOT NULL',
        self::TICKET_PRICE . ' BIGINT UNSIGNED NOT NULL DEFAULT 0',
        self::BOOKING_ID . ' BIGINT UNSIGNED NOT NULL',
        self::FIRST_NAME . ' VARCHAR(191) NULL DEFAULT NULL',
        self::LAST_NAME . ' VARCHAR(191) NULL DEFAULT NULL',
        self::STATUS . " VARCHAR(32) NOT NULL DEFAULT 'active'",
        self::CHECKIN_AT . ' TIMESTAMP NULL DEFAULT NULL',
        self::METADATA . ' JSON NULL',
        'PRIMARY KEY  (' . self::ID . ')',
		'INDEX (' . self::BOOKING_ID . ')'
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
