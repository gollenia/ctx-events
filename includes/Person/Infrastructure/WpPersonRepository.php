<?php
declare(strict_types=1);

namespace Contexis\Events\Person\Infrastructure;

use Contexis\Events\Person\Domain\Person;
use Contexis\Events\Person\Domain\PersonCollection;
use Contexis\Events\Person\Domain\PersonCriteria;
use Contexis\Events\Person\Domain\PersonId;
use Contexis\Events\Person\Domain\PersonRepository;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

class WpPersonRepository implements PersonRepository
{
    public const POST_TYPE_CLASS = PersonPost::class;

    public function get(PersonId $id): Person
    {
        $snapshot = PostSnapshot::fromWpPostId($id->toInt());
        return PersonMapper::map($snapshot);
    }

    public function find(?PersonId $id): ?Person
    {
        $snapshot = PostSnapshot::fromWpPostId($id->toInt());
        return PersonMapper::map($snapshot);
    }

    public function first(PersonCriteria $criteria): ?Person
    {
        $builder = WpPersonQueryBuilder::fromCriteria($criteria)
            ->withCache()
            ->addArg('posts_per_page', 1)
            ->addArg('no_found_rows', true);

        $wpq = new \WP_Query($builder->getArgs());

        return $wpq->have_posts()
            ? PersonMapper::map(new PostSnapshot($wpq->posts[0]))
            : null;
    }

    public function findByIds(array $ids): PersonCollection
    {
        $builder = WpPersonQueryBuilder::fromCriteria(new PersonCriteria())
            ->withCache()
            ->addArg('post__in', array_map(fn(PersonId $id) => $id->toInt(), $ids))
            ->addArg('posts_per_page', -1);

        $wpq = new \WP_Query($builder->getArgs());

        $persons = [];

        foreach ($wpq->posts as $post) {
            $persons[] = PersonMapper::map(new PostSnapshot($post));
        }

        return PersonCollection::from(...$persons);
    }

    public function search(PersonCriteria $criteria): PersonCollection
    {
        $builder = WpPersonQueryBuilder::fromCriteria($criteria)
            ->withCache();

        $wpq = new \WP_Query($builder->getArgs());

        $persons = [];
        foreach ($wpq->posts as $post) {
            $persons[] = PersonMapper::map(new PostSnapshot($post));
        }

        return PersonCollection::from(...$persons);
    }

    public function count(PersonCriteria $criteria): int
    {
        $builder = WpPersonQueryBuilder::fromCriteria($criteria)
            ->withCache()
            ->addArg('fields', 'ids')
            ->addArg('no_found_rows', false)
            ->addArg('posts_per_page', -1);

        $wpq = new \WP_Query($builder->getArgs());

        return (int)$wpq->found_posts;
    }
}
