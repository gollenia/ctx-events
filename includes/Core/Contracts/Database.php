<?php

namespace Contexis\Events\Core\Contracts;

interface Database
{
    public function query(string $sql): array;
    public function prepare(string $sql, ...$args): string;
    public function execute(string $sql, array $params = []): int;
}
