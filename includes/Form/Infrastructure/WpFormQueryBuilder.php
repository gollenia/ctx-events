<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Infrastructure;

use Contexis\Events\Form\Application\DTOs\FormCriteria;
use Contexis\Events\Form\Domain\Enums\FormType;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;
use Contexis\Events\Shared\Infrastructure\Abstracts\WpQueryBuilder;
use Contexis\Events\Shared\Infrastructure\ValueObjects\OrderBy;

final class WpFormQueryBuilder extends WpQueryBuilder
{
    public static function fromCriteria(FormCriteria $criteria): self
    {
        $builder = new self()
            ->withPostType(self::getPostTypes($criteria))
            ->withPagination($criteria->page, $criteria->perPage)
            ->withStatus($criteria->status ?? StatusList::public())
            ->withTaxonomy(BookingFormPost::TAGS, $criteria->tags);

		if($criteria->search) {
			$builder = $builder->withSearch($criteria->search);
		}


        $orderBy = self::mapOrderBy($criteria->orderBy);
        $builder = $builder->orderBy($orderBy);

        return $builder;
    }

    private static function mapOrderBy(OrderBy $orderBy): OrderBy
    {
		
        return match ($orderBy->field) {
			'title'      => OrderBy::fromField('post_title', $orderBy->order),
            'date'     => OrderBy::fromField('post_date', $orderBy->order),
            'type'  => OrderBy::fromField('post_type', $orderBy->order),
            default         => OrderBy::fromField('post_date', $orderBy->order),
        };
    }

	private static function getPostTypes(FormCriteria $criteria): array|string
	{
		if ($criteria->type === null) {
			return [AttendeeFormPost::POST_TYPE, BookingFormPost::POST_TYPE];
		}

		if($criteria->type === FormType::ATTENDEE) {
			return AttendeeFormPost::POST_TYPE;
		}

		return BookingFormPost::POST_TYPE;
	}
 
}
