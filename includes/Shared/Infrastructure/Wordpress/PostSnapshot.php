<?php

namespace Contexis\Events\Shared\Infrastructure\Wordpress;

use DateTimeImmutable;
use DateTimeZone;
use WP_Post;

final class PostSnapshot
{
    private array $meta;

    public function __construct(private readonly WP_Post $post)
    {
        $this->meta = get_post_meta($post->ID);
    }

    public static function fromWpPostId(int $id): ?PostSnapshot
    {
        if (!$id) {
            return null;
        }
        $post = get_post($id);
        if (!$post) {
            return null;
        }

        return new PostSnapshot($post);
    }


    public function __get(string $key): mixed
    {
        if ($key === 'id') {
            return $this->post->ID;
        }
        return $this->post->$key ?? null;
    }

    public function has(string $key): bool
    {
        return property_exists($this->post, $key) || $this->getRealMetaKey($key) !== null;
    }

    private function rawValue(string $key): mixed
    {
        if (property_exists($this->post, $key)) {
            return $this->post->$key;
        }
        $real = $this->getRealMetaKey($key);
        if ($real !== null && isset($this->meta[$real][0])) {
            $v = maybe_unserialize($this->meta[$real][0]);
            return $v === false ? null : $v;
        }
        return null;
    }

    /** @template T @param T $default @return T */
    public function getValueOr(string $key, mixed $default): mixed
    {
        $v = $this->rawValue($key);
        return $v !== null ? $v : $default;
    }

    public function getThumbnailId(): ?int
    {
        $id = get_post_thumbnail_id($this->post->ID);
        return $id ? (int)$id : null;
    }

    public function getString(string $key, ?string $default = null): ?string
    {
        $v = $this->rawValue($key);
        if ($v === null) {
            return $default;
        }
        if (is_scalar($v) || (is_object($v) && method_exists($v, '__toString'))) {
            $s = (string)$v;
            return $s !== '' ? $s : $default;
        }
        return $default;
    }

    public function getInt(string $key, ?int $default = null): ?int
    {
        $v = $this->rawValue($key);
        return is_numeric($v) ? (int)$v : $default;
    }

    public function getFloat(string $key, ?float $default = null): ?float
    {
        $v = $this->rawValue($key);
        return is_numeric($v) ? (float)$v : $default;
    }

    public function getBool(string $key, ?bool $default = null): ?bool
    {
        $v = $this->rawValue($key);
        if ($v === null) {
            return $default;
        }

        if (is_bool($v)) {
            return $v;
        }

        if (is_string($v)) {
            $b = filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            return $b ?? $default;
        }

        if (is_int($v)) {
            return $v !== 0;
        }
        if (is_float($v)) {
            return $v != 0.0;
        }

        return $default;
    }

    public function getArray(string $key, ?array $default = null): ?array
    {
        $v = $this->rawValue($key);
        if ($v === null) {
            return $default;
        }
        if (is_array($v)) {
            return $v;
        }

        if (is_string($v)) {
            $json = json_decode($v, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                return $json;
            }
            if (str_contains($v, ',')) {
                return array_map('trim', explode(',', $v));
            }
        }
        return $default;
    }

    public function getDateTime(string $key, ?DateTimeZone $tz = null, ?DateTimeImmutable $default = null): ?DateTimeImmutable
    {
        $v = $this->rawValue($key);
        if ($v === null || $v === '') {
            return $default;
        }

        // Unix-Timestamp erlauben
        if (is_numeric($v) && (int)$v > 0 && strlen((string)$v) >= 10) {
            try {
                return (new DateTimeImmutable('@' . (int)$v))->setTimezone($tz ?? new DateTimeZone(date_default_timezone_get()));
            } catch (\Throwable) {
                return $default;
            }
        }

        try {
            return new DateTimeImmutable((string)$v, $tz);
        } catch (\Throwable) {
            return $default;
        }
    }

    /** @return mixed|null */
    public function getMetaValue(string $key, mixed $default = null): mixed
    {
        $real = $this->getRealMetaKey($key);
        if ($real === null) {
            return $default;
        }
        $v = $this->meta[$real][0] ?? null;
        $v = $v !== null ? maybe_unserialize($v) : null;
        return $v === false ? $default : ($v ?? $default);
    }

    private function getRealMetaKey(string $key): ?string
    {
        $_key = str_starts_with($key, '_') ? $key : "_$key";
        if (array_key_exists($_key, $this->meta)) {
            return $_key;
        }
        return array_key_exists($key, $this->meta) ? $key : null;
    }
}
