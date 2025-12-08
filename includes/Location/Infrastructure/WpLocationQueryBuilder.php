<?php
declare(strict_types=1);

namespace Contexis\Events\Location\Infrastructure;

use Contexis\Events\Location\Domain\LocationCriteria;
use Contexis\Events\Location\Infrastructure\LocationPost;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;
use Contexis\Events\Shared\Infrastructure\Abstracts\WpQueryBuilder;

final class WpLocationQueryBuilder extends WpQueryBuilder
{
    public static function fromCriteria(LocationCriteria $criteria): self
    {
        $builder = new self()
            ->withPostType(LocationPost::POST_TYPE)
            ->withPagination($criteria->page, $criteria->perPage)
            ->withStatus($criteria->status ?? StatusList::public())
            ->withMetaEquals('_latitude', (string) $criteria->latitude)
            ->withMetaEquals('_longitude', (string) $criteria->longitude)
            ->withSearch($criteria->search ?? '');

        return $builder;
    }
}
