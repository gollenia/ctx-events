<?php

namespace Contexis\Events\Infrastructure\Persistence;

use Contexis\Events\Domain\Repositories\ImageRepository;
use Contexis\Events\Domain\ValueObjects\Id\AbstractId;
use Contexis\Events\Domain\ValueObjects\Id\ImageId;
use Contexis\Events\Domain\ValueObjects\Media;
use Contexis\Events\Domain\ValueObjects\ImageSizes;
use Contexis\Events\Domain\ValueObjects\Image;

final class WpImageRepository extends WpAbstractRepository implements ImageRepository
{
    public function getPostType(): string
    {
        return 'attachment';
    }

    public function find(?ImageId $id): ?Image
    {
        $post = parent::getSnapshot($id);
        $metadata = wp_get_attachment_metadata($id->toInt());

        //var_dump(wp_get_registered_image_subsizes());
        return new \Contexis\Events\Domain\ValueObjects\Image(
            url: wp_get_attachment_url($id->toInt()),
            alt_text: $metadata['_wp_attachment_image_alt'] ?? '',
            width: $metadata['width'] ?? 0,
            height: $metadata['height'] ?? 0,
            mimeType: $metadata['mime_type'] ?? '',
            sizes: $this->getImageSizes($id->toInt())
        );
    }

    private function getImageSizes(int $attachmentId): ImageSizes
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
