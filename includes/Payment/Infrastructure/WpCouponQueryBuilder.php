<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Payment\Application\Dtos\CouponCriteria;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;
use Contexis\Events\Shared\Infrastructure\Abstracts\WpQueryBuilder;
use Contexis\Events\Shared\Infrastructure\ValueObjects\OrderBy;

final class WpCouponQueryBuilder extends WpQueryBuilder
{
    public static function fromCriteria(CouponCriteria $criteria): self
    {
        $builder = (new self())
            ->withPostType(CouponPost::POST_TYPE)
            ->withPagination($criteria->page, $criteria->perPage)
            ->withStatus($criteria->status ?? StatusList::defaultAdmin());

        if ($criteria->search !== null && $criteria->search !== '') {
            $builder = $builder->withSearch($criteria->search);
        }

        return $builder->orderBy(self::mapOrderBy($criteria->orderBy));
    }

    private static function mapOrderBy(OrderBy $orderBy): OrderBy
    {
        return match ($orderBy->field) {
            'title' => OrderBy::fromField('post_title', $orderBy->order),
            'status' => OrderBy::fromField('post_status', $orderBy->order),
            default => OrderBy::fromField('post_date', $orderBy->order),
        };
    }
}
