<?php

namespace Contexis\Events\Person\Infrastructure;

use Contexis\Events\Person\Domain\Person;
use Contexis\Events\Person\Domain\PersonCriteria;
use Contexis\Events\Person\Infrastructure\PersonPost;
use Contexis\Events\Shared\Infrastructure\Abstracts\WpQueryBuilder;

final class WpPersonQueryBuilder extends WpQueryBuilder
{
    public static function fromCriteria(PersonCriteria $criteria): self
    {
        $builder = new self()
            ->withPostType(PersonPost::POST_TYPE)
            ->withPagination($criteria->page, $criteria->perPage)
            ->withTaxonomy(PersonPost::CATEGORY, $criteria->categories)
            ->withStatus($criteria->status);
        return $builder;
    }
}
