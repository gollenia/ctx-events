<?php

namespace Contexis\Events\Domain\ValueObjects;

final class AttendeeMeta
{
    public function __construct(
        private array $meta = []
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->meta[$key] ?? $default;
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function all(): array
    {
        return $this->meta;
    }
}
