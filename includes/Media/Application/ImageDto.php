<?php

namespace Contexis\Events\Media\Application;

use Contexis\Events\Media\Domain\Image;
use Contexis\Events\Media\Domain\ImageSizes;

final class ImageDto
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

    public static function fromDomainModel(Image $media): self
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
