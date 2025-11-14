<?php

namespace Contexis\Events\Media\Infrastructure;

use Contexis\Events\Media\Domain\Image;
use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Media\Domain\ImageRepository;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;
use WP_Post;

final class WpImageRepository implements ImageRepository
{
    public function getPostType(): string
    {
        return 'attachment';
    }

    public function find(?ImageId $id): ?Image
    {
        $snapshot = PostSnapshot::fromWpPostId($id?->toInt());
        return ImageMapper::map($snapshot);
    }
}
