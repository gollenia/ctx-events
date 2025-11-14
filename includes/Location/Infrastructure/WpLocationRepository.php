<?php

namespace Contexis\Events\Location\Infrastructure;

use Contexis\Events\Location\Domain\Location;
use Contexis\Events\Location\Domain\LocationCollection;
use Contexis\Events\Location\Domain\LocationCriteria;
use Contexis\Events\Location\Domain\LocationId;
use Contexis\Events\Location\Domain\LocationRepository;
use Contexis\Events\Person\Infrastructure\WpLocationQueryBuilder;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

class WpLocationRepository implements LocationRepository
{
    public const POST_TYPE_CLASS = LocationPost::class;

    public function get(LocationId $id): Location
    {
        $snapshot = PostSnapshot::fromWpPostId($id->toInt());
        return LocationMapper::map($snapshot);
    }

    public function find(?LocationId $id): ?Location
    {
        $snapshot = PostSnapshot::fromWpPostId($id->toInt());
        return LocationMapper::map($snapshot);
    }

    public function first(LocationCriteria $criteria): ?Location
    {
        $builder = WpLocationQueryBuilder::fromCriteria($criteria)
            ->withCache()
            ->addArg('posts_per_page', 1)
            ->addArg('no_found_rows', true);

        $wpq = new \WP_Query($builder->getArgs());

        return $wpq->have_posts()
            ? LocationMapper::map(new PostSnapshot($wpq->posts[0]))
            : null;
    }

    public function search(LocationCriteria $criteria): LocationCollection
    {
        $builder = WpLocationQueryBuilder::fromCriteria($criteria)
            ->withCache();

        $wpq = new \WP_Query($builder->getArgs());

        $locations = new LocationCollection();
        foreach ($wpq->posts as $post) {
            $locations->add(LocationMapper::map(new PostSnapshot($post)));
        }

        return $locations;
    }

    public function count(LocationCriteria $criteria): int
    {
        $builder = WpLocationQueryBuilder::fromCriteria($criteria)
            ->withCache()
            ->addArg('fields', 'ids')
            ->addArg('no_found_rows', false)
            ->addArg('posts_per_page', -1);

        $wpq = new \WP_Query($builder->getArgs());

        return (int)$wpq->found_posts;
    }
}
