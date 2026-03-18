<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Wordpress;

use Contexis\Events\Shared\Application\Contracts\OptionsReader;

abstract class WpOptions implements OptionsReader
{
	/** @var array<string, mixed> */
    protected array $fields = [];

	/**
	 * @return array<string, mixed>
	 */
    abstract protected function fields() : array;

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
