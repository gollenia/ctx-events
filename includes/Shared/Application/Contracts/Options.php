<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Application\Contracts;

interface Options
{
    public function getBool(string $key, bool $default = false): bool;

    public function getString(string $key, ?string $default = null): ?string;

    public function getInt(string $key, int $default = 0): int;
}
