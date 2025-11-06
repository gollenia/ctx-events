<?php

namespace Contexis\Events\Infrastructure\PostTypes;

use WP_Post;

final class PostSnapshot
{
    private array $meta;

    public function __construct(
        private readonly WP_Post $post
    ) {
        $this->meta = get_post_meta($post->ID);
    }

    public function __get(string $key): mixed
    {
        if ($key === 'id') {
            return $this->post->ID;
        }
        return isset($this->post->$key) ? $this->post->$key : null;
    }

    public function getThumbnailId(): ?int
    {
        $thumb_id = get_post_thumbnail_id($this->post->ID);
        return $thumb_id ? (int)$thumb_id : null;
    }

    public function getMetaValue(string $key): mixed
    {
        $realKey = $this->getRealMetaKey($key);
        if ($realKey === null) {
            return null;
        }
        return maybe_unserialize($this->meta[$realKey][0]) ?? null;
    }

    private function getRealMetaKey(string $key): ?string
    {
        $_key = str_starts_with($key, '_') ? $key : "_$key";
        if (array_key_exists($_key, $this->meta)) {
            return $_key;
        }

        return  array_key_exists($key, $this->meta) ? $key : null;
    }
}
