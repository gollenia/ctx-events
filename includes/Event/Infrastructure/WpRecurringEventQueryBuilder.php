<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Domain\OccurrenceCriteria;
use Contexis\Events\Event\Domain\Enums\EventStatus;
use Contexis\Events\Shared\Infrastructure\Abstracts\WpQueryBuilder;

/** Builds only the WordPress candidate query; exact window matching stays in the repository. */
final class WpRecurringEventQueryBuilder extends WpQueryBuilder
{
    public static function fromCriteria(OccurrenceCriteria $criteria): self
    {
        $builder = (new self())
            ->withPostType(RecurringEventPost::POST_TYPE)
            ->withTaxonomy(EventTaxonomy::CATEGORIES, $criteria->filters->categories)
            ->withTaxonomy(EventTaxonomy::TAGS, $criteria->filters->tags)
            ->addArg('post_status', array_map(
                static fn (EventStatus $status): string => $status->value,
                $criteria->statuses->all(),
            ))
            ->addArg('posts_per_page', -1)
            ->addArg('no_found_rows', true);

        if ($criteria->recurrenceId !== null) {
            $builder = $builder->addArg('post__in', [$criteria->recurrenceId->toInt()]);
        }

        if ($criteria->filters->locationId !== null) {
            $builder = $builder->withMetaEquals(RecurringEventMeta::LOCATION_ID, (string) $criteria->filters->locationId);
        }

        if ($criteria->filters->personId !== null) {
            $builder = $builder->withMetaEquals(RecurringEventMeta::PERSON_ID, (string) $criteria->filters->personId);
        }

        if ($criteria->filters->search !== null) {
            $builder = $builder->withSearch($criteria->filters->search);
        }

        return $builder;
    }
}
