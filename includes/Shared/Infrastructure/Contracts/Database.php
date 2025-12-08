<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Contracts;

interface Database
{
    public function query(string $sql): array;
    public function prepare(string $sql, ...$args): string;
    public function execute(string $sql, array $params = []): int;
}
