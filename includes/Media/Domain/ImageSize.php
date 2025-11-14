<?php

namespace Contexis\Events\Media\Domain;

final readonly class ImageSize implements \JsonSerializable
{
    public function __construct(
        private string $url,
        private int $width,
        private int $height
    ) {
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function jsonSerialize(): array
    {
        return [
            'url' => $this->url,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
