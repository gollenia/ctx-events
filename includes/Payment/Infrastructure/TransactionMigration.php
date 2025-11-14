<?php

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Contracts\Migration;

final class TransactionMigration implements Migration
{
    private string $table_name = 'ctx_event_transactions';

    private array $columns = [
        'id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
        'external_id VARCHAR(100) NOT NULL',
        'booking_id BIGINT UNSIGNED NOT NULL',
        'amount BIGINT NOT NULL DEFAULT 0',
        'currency VARCHAR(10) NOT NULL',
        'gateway VARCHAR(50) NOT NULL',
        'status TINYINT NOT NULL DEFAULT 0',
        'transaction_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'expires_at TIMESTAMP NULL',
        'created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'details JSON NULL',
        'PRIMARY KEY  (id)'
    ];

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getColumnsAsString(): string
    {
        return implode(",\n  ", $this->columns);
    }

    public function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . $this->table_name;
    }
}
