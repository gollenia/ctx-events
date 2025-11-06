<?php

namespace Contexis\Events\Infrastructure\Persistence\Mappers;

use Contexis\Events\Domain\ValueObjects\Image;

final class ImageWrapper
{
    public static function map(array $data): Image
    {
        return new Image(
            url: $data['url'] ?? null,
            alt_text: $data['alt_text'] ?? null,
            width: $data['width'] ?? null,
            height: $data['height'] ?? null,
            mimeType: $data['mimeType'] ?? null,
            sizes: isset($data['sizes']) ? ImageSizesWrapper::toDomain($data['sizes']) : null
        );
    }
}
