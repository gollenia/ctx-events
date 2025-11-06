<?php

namespace Contexis\Events\Domain\ValueObjects;

final readonly class ImageSizes implements \JsonSerializable
{
    // @var array<string, ImageSize>
    private array $sizes;

    public function __construct(
        array $sizes = []
    ) {
        $this->sizes = $sizes;
    }

    public function getSize(string $key): ?ImageSize
    {
        return $this->sizes[$key] ?? null;
    }

    public function jsonSerialize(): array
    {
        return $this->sizes;
    }
}
