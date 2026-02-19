<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Contracts;

use Contexis\Events\Shared\Infrastructure\Enums\DatabaseOutput;

interface Database 
{
	public function query(string $query): int|bool;

	public function getResults(string $query, DatabaseOutput $output = DatabaseOutput::OBJECT): array;

	public function prepare(string $query, ...$args): string;
    public function getPrefix(): string;

	public function getVar(string $query, int $x = 0, int $y = 0): string|null;

	public function getInt(string $query, int $x = 0, int $y = 0): int;

	public function getRow(string|null $query, DatabaseOutput $output = DatabaseOutput::OBJECT, int $y = 0): array|object|null;

	public function getCol(string|null $query, int $x = 0): array;

	public function insert(string $table, array $data, array $format = []): int|false;
	

	public function replace(string $table, array $data, array $format = []): int|false;

	public function update(string $table, array $data, array $where, array $format = [], array $whereFormat = []): int|false;

	public function delete(string $table, array $where, array $whereFormat = []): int|false;
	
}