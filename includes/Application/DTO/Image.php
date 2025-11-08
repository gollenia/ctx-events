<?php

namespace Contexis\Events\Application\DTO;

use Contexis\Events\Domain\ValueObjects\ImageSizes;

final class Image
{
    public function __construct(
        public readonly ?string $url,
        public readonly ?string $altText,
        public readonly ?int $width,
        public readonly ?int $height,
        public readonly ?string $mimeType,
        public readonly ?ImageSizes $sizes = null
    ) {
    }

    public static function fromDomainModel(\Contexis\Events\Domain\ValueObjects\Image $media): self
    {
        return new self(
            url: $media->url,
            altText: $media->altText,
            width: $media->width,
            height: $media->height,
            mimeType: $media->mimeType,
            sizes: $media->sizes
        );
    }
}
