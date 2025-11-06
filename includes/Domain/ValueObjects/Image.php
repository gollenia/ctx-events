<?php

namespace Contexis\Events\Domain\ValueObjects;

final class Image implements \JsonSerializable
{
    public function __construct(
        public readonly ?string $url,
        public readonly ?string $alt_text,
        public readonly ?int $width,
        public readonly ?int $height,
        public readonly ?string $mimeType,
        public readonly ?ImageSizes $sizes = null
    ) {
    }

    public function url(string $size = 'original'): string
    {
        return $this->sizes?->getSize($size)?->getUrl() ?? $this->url ?? '';
    }

    public function jsonSerialize(): array
    {
        return [
            'url' => $this->url,
            'alt_text' => $this->alt_text,
            'width' => $this->width,
            'height' => $this->height,
            'mimetype' => $this->mimeType,
            'sizes' => $this->sizes
        ];
    }
}
