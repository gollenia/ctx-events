<?php

namespace Contexis\Events\Infrastructure\Persistence;

use Contexis\Events\Domain\Repositories\ImageRepository;
use Contexis\Events\Domain\ValueObjects\Id\AbstractId;
use Contexis\Events\Domain\ValueObjects\Id\ImageId;
use Contexis\Events\Domain\ValueObjects\Media;
use Contexis\Events\Domain\ValueObjects\ImageSizes;
use Contexis\Events\Domain\ValueObjects\Image;
use Contexis\Events\Infrastructure\Persistence\Mappers\ImageMapper;

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

        return ImageMapper::map($post);
    }
}
