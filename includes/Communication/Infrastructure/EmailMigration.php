<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Contracts\Migration;

final class EmailMigration implements Migration
{
    private const TABLE_NAME = 'ctx_event_emails';

    public const ID = 'id';
    public const EVENT_ID = 'event_id';
    public const TRIGGER = 'email_trigger';
    public const TARGET = 'email_target';
    public const ENABLED = 'enabled';
    public const GATEWAY = 'gateway';
    public const SUBJECT = 'subject';
    public const BODY = 'body';
    public const REPLY_TO = 'reply_to';
    public const CREATED_AT = 'created_at';

    /**
     * @var list<string>
     */
    private array $columns = [
        self::ID . ' BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
        self::EVENT_ID . ' BIGINT UNSIGNED NULL',
        self::TRIGGER . ' VARCHAR(100) NOT NULL',
        self::TARGET . ' VARCHAR(100) NOT NULL',
        self::ENABLED . ' TINYINT(1) NOT NULL DEFAULT 1',
        self::GATEWAY . ' VARCHAR(100) NULL',
        self::SUBJECT . ' TEXT NULL',
        self::BODY . ' LONGTEXT NOT NULL',
        self::REPLY_TO . ' VARCHAR(191) NULL',
        self::CREATED_AT . ' TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'PRIMARY KEY  (' . self::ID . ')',
        'INDEX (' . self::EVENT_ID . ')',
        'INDEX (' . self::TRIGGER . ')',
        'INDEX (' . self::TARGET . ')',
        'INDEX (' . self::ENABLED . ')',
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
