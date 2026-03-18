<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Contracts;

interface Migration
{
    public static function getTableName(): string;
	/** @return array<mixed> */
    public function getColumns(): array;
    public function getColumnsAsString(): string;
}
