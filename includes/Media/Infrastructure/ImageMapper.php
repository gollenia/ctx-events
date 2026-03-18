<?php
declare(strict_types=1);

namespace Contexis\Events\Media\Infrastructure;

use Contexis\Events\Media\Domain\Image;
use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Media\Domain\ImageSize;
use Contexis\Events\Media\Domain\ImageSizes;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

final class ImageMapper
{
    public static function map(PostSnapshot $post): Image
    {
        return new Image(
            id: ImageId::from($post->id),
            url: wp_get_attachment_url($post->id),
            altText: $post->getString('_wp_attachment_image_alt'),
            width: $post->getInt('width'),
            height: $post->getInt('height'),
            mimeType: $post->getString('mime_type'),
            sizes: self::getImageSizes($post->id)
        );
    }

    private static function getImageSizes(int $attachmentId): ImageSizes
    {
        $sizes = wp_get_registered_image_subsizes();
		$imageSizes = [];
        foreach ($sizes as $sizeName => $sizeData) {
            $imageSrc = wp_get_attachment_image_src($attachmentId, $sizeName);
            if ($imageSrc) {
                $imageSizes[$sizeName] = new ImageSize(
                    url: $imageSrc[0],
                    width: $imageSrc[1],
                    height: $imageSrc[2]
                );
            }
        }
        return new ImageSizes($imageSizes);
    }
}
