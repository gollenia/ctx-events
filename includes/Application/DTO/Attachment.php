<?php

namespace Contexis\Events\Application\DTO;

use Contexis\Events\Domain\ValueObjects\ImageSizes;

final class Attachment
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

    public static function fromDomainModel(\Contexis\Events\Domain\ValueObjects\Attachment $media): self
    {
        return new self(
            url: $media->url,
            alt_text: $media->alt_text,
            width: $media->width,
            height: $media->height,
            mimeType: $media->mimeType,
            sizes: $media->sizes ? new ImageSizes(
                thumbnail: $media->sizes->thumbnail,
                medium: $media->sizes->medium,
                large: $media->sizes->large,
                original: $media->sizes->original
            ) : new ImageSizes(null, null, null, null)
        );
    }
}
