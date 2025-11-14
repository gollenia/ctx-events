<?php

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Contracts\Migration;

final class AttendeeMigration implements Migration
{
    private string $table_name = 'ctx_event_attendees';

    private array $columns = [
        'id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
        'ticket_id VARCHAR(100) NOT NULL',
        'booking_id BIGINT UNSIGNED NOT NULL',
        'first_name VARCHAR(100) NOT NULL',
        'last_name VARCHAR(100) NOT NULL',
        'email VARCHAR(200) NOT NULL',
        'metadata JSON NULL',
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
