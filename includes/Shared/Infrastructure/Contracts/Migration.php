<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Contracts;

interface Migration
{
    public static function getTableName(): string;
    public function getColumns(): array;
    public function getColumnsAsString(): string;
}
