<?php

namespace Contexis\Events\Infrastructure\Persistence\Mappers;

use Contexis\Events\Domain\ValueObjects\Image;
use Contexis\Events\Infrastructure\PostTypes\PostSnapshot;
use Contexis\Events\Domain\ValueObjects\ImageSizes;

final class ImageMapper
{
    public static function map(PostSnapshot $post): Image
    {
        return new Image(
            url: wp_get_attachment_url($post->id->toInt()),
            altText: $post->getString('_wp_attachment_image_alt'),
            width: $post->getInt('width'),
            height: $post->getInt('height'),
            mimeType: $post->getString('mime_type'),
            sizes: self::getImageSizes($post->id->toInt())
        );
    }

    private static function getImageSizes(int $attachmentId): ImageSizes
    {
        $sizes = wp_get_registered_image_subsizes();
        foreach ($sizes as $sizeName => $sizeData) {
            $imageSrc = wp_get_attachment_image_src($attachmentId, $sizeName);
            if ($imageSrc) {
                $imageSizes[$sizeName] = new \Contexis\Events\Domain\ValueObjects\ImageSize(
                    url: $imageSrc[0],
                    width: $imageSrc[1],
                    height: $imageSrc[2]
                );
            }
        }
        return new ImageSizes($imageSizes);
    }
}
