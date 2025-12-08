<?php
declare(strict_types=1);

namespace Contexis\Events\Media\Infrastructure;

use Contexis\Events\Media\Domain\Image;
use Contexis\Events\Media\Domain\ImageCollection;
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

    public function findByIds(array $ids): ImageCollection
    {
        $wpq = new \WP_Query([
            'post_type'      => $this->getPostType(),
            'post__in'       => array_map(fn(ImageId $id) => $id->toInt(), $ids),
            'posts_per_page' => -1,
            'no_found_rows'  => true,
            'fields'         => 'all',
            'post_status' => 'inherit'
        ]);


        $items = [];
        foreach ($wpq->posts as $post) {
            $items[] = ImageMapper::map(new PostSnapshot($post));
        }

        return new ImageCollection(...$items);
    }
}
