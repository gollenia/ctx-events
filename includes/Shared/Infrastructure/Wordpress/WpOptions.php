<?php

namespace Contexis\Events\Shared\Infrastructure\Wordpress;

use Contexis\Events\Shared\Application\Contracts\Options;

abstract class WpOptions implements Options
{
    protected array $fields = [];

    abstract protected function fields();

    public function getBool(string $key, bool $default = false): bool
    {

        $value = get_option($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) (int) $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            ?? $default;
    }

    public function getString(string $key, ?string $default = null): ?string
    {
        $value = get_option($key, $default);

        return $value !== false ? (string) $value : $default;
    }

    public function getInt(string $key, int $default = 0): int
    {
        $value = get_option($key, $default);

        if ($value === false) {
            return $default;
        }

        return (int) $value;
    }
}
