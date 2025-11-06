<?php

namespace Contexis\Events\Domain\ValueObjects;

final class Attachment implements \JsonSerializable
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
        return match ($size) {
            'thumbnail' => $this->sizes->thumbnail,
            'medium'    => $this->sizes->medium ?? $this->url,
            'large'     => $this->sizes->large ?? $this->url,
            default     => $this->url,
        };
    }

    public function jsonSerialize(): array
    {
        return [
            'url' => $this->url,
            'alt_text' => $this->alt_text,
            'width' => $this->width,
            'height' => $this->height,
            'mimetype' => $this->mimeType,
            'sizes' => $this->sizes?->toArray(),
        ];
    }
}
