<?php

namespace Contexis\Events\Infrastructure\Persistence;

use Contexis\Events\Domain\Collections\LocationCollection;
use Contexis\Events\Domain\Models\Location;
use Contexis\Events\Domain\Repositories\LocationRepository;
use Contexis\Events\Domain\ValueObjects\Id\LocationId;
use Contexis\Events\Infrastructure\PostTypes\LocationPost;
use Contexis\Events\Infrastructure\Persistence\Mappers\LocationMapper;
use Contexis\Events\Infrastructure\PostTypes\PostSnapshot;

class WpLocationRepository extends WpAbstractRepository implements LocationRepository
{
    public const POST_TYPE_CLASS = LocationPost::class;

    public function get(): LocationCollection
    {
        $posts = parent::get();
        $locations = new LocationCollection();
        foreach ($posts as $post) {
            $locations->add(LocationMapper::map(new PostSnapshot($post)));
        }
        return $locations;
    }

    public function find(?LocationId $id): ?Location
    {
        $post = parent::getSnapshot($id);

        return $post ? LocationMapper::map($post) : null;
    }

    public function first(): ?Location
    {
        $post = parent::first();
        if (!$post) {
            return null;
        }
        return LocationMapper::map($post);
    }


    protected function getQueryArgs(array $args): array
    {
        $queryArgs = [
            'post_type'      => self::POST_TYPE_CLASS::POST_TYPE,
            'posts_per_page' => -1,
        ];

        return array_merge($queryArgs, $args);
    }
}
