<?php

namespace Contexis\Events\Infrastructure\Persistence;

use Contexis\Events\Domain\Models\Person;
use Contexis\Events\Infrastructure\PostTypes\PersonPost;
use Contexis\Events\Infrastructure\Persistence\Mappers\PersonMapper;
use Contexis\Events\Domain\Collections\PersonCollection;
use Contexis\Events\Domain\ValueObjects\Id\PersonId;
use Contexis\Events\Domain\Repositories\PersonRepository;
use Contexis\Events\Infrastructure\Persistence\Mappers\ContactMapper;
use Contexis\Events\Infrastructure\PostTypes\PostSnapshot;

class WpPersonRepository extends WpAbstractRepository implements PersonRepository
{
    public const POST_TYPE_CLASS = PersonPost::class;

    public function get(): PersonCollection
    {
        $posts = parent::get();
        $persons = new PersonCollection();
        foreach ($posts as $post) {
            $persons->add(PersonMapper::map(new PostSnapshot($post)));
        }
        return $persons;
    }

    public function find(?PersonId $id): ?Person
    {
        $post = parent::getSnapshot($id);
        return $post ? PersonMapper::map($post) : null;
    }

    public function first(): ?Person
    {
        $post = parent::first();
        if (!$post) {
            return null;
        }
        return PersonMapper::map($post);
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
