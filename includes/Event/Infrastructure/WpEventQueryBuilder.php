<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Application\Contracts\EventOptions;
use Contexis\Events\Event\Application\EventCriteria;
use Contexis\Events\Event\Application\EventPageCriteria;
use Contexis\Events\Event\Domain\Enums\TimeScope;
use Contexis\Events\Shared\Infrastructure\ValueObjects\OrderBy;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;
use Contexis\Events\Shared\Infrastructure\Abstracts\WpQueryBuilder;

final class WpEventQueryBuilder extends WpQueryBuilder
{
    public static function fromCriteria(EventCriteria $criteria): self
    {
        $builder = new self()
            ->withPostType(EventPost::POST_TYPE)
            ->withPagination($criteria->page, $criteria->perPage)
            ->withStatus($criteria->status ?? StatusList::public())
            ->withTaxonomy(EventPost::CATEGORIES, $criteria->categories)
            ->withTaxonomy(EventPost::TAGS, $criteria->tags)
        ;

        if ($criteria->location !== null) {
            $builder = $builder->withMetaEquals(EventMeta::LOCATION_ID, (string) $criteria->location);
        }

        if ($criteria->bookable) {
            $builder = $builder->withMetaEquals(EventMeta::BOOKING_ENABLED, '1');
        }

        if ($criteria->person != null) {
            $builder = $builder->withMetaEquals(EventMeta::PERSON_ID, (string) $criteria->person);
        }

        if ($criteria->scope) {
            foreach (self::dateScopeToMetaQuery($criteria->scope) as $cond) {
                $builder->args['meta_query'][] = $cond;
            }
        }

		$builder = match ($criteria->isFree) {
			true    => $builder->withMetaEquals(EventMeta::CACHED_MIN_PRICE, '0'),
			false   => $builder->withMetaCompare(EventMeta::CACHED_MIN_PRICE, '0', '>', 'NUMERIC'),
			default => $builder,
		};

        $orderBy = self::mapOrderBy($criteria->orderBy)->withOrder($criteria->order);
        $builder = $builder->orderBy($orderBy);

        return $builder;
    }


    private static function mapOrderBy(string $orderBy): OrderBy
    {
        return match ($orderBy) {
            'date-time'     => OrderBy::fromMeta(EventMeta::EVENT_START),
            'booking-date'  => OrderBy::fromMeta(EventMeta::BOOKING_START),
            'booking'       => OrderBy::fromMeta(EventMeta::BOOKING_ENABLED),
            'location'      => OrderBy::fromMeta(EventMeta::LOCATION_ID),
            'person'        => OrderBy::fromMeta(EventMeta::PERSON_ID),
            'price'         => OrderBy::fromMeta(EventMeta::CACHED_MIN_PRICE),
            default         => OrderBy::fromField($orderBy),
        };
    }

    private static function dateScopeToMetaQuery(TimeScope $scope): array
    {
        $now = new \DateTimeImmutable('now', wp_timezone());
        $date = $now->format('Y-m-d');
        $dateTime = $now->format('Y-m-d H:i:s');
        $field = get_option(WpEventOptions::EVENT_ONGOING_IS_PAST) ? EventMeta::EVENT_END : EventMeta::EVENT_START;

        return match ($scope) {
            $scope::PAST    => [['key' => $field, 'value' => $dateTime, 'compare' => '<', 'type' => 'DATETIME']],
            $scope::FUTURE  => [['key' => $field, 'value' => $dateTime, 'compare' => '>', 'type' => 'DATETIME']],
            $scope::TODAY   => [
                ['key' => EventMeta::EVENT_START, 'value' => $date, 'compare' => '<=', 'type' => 'DATE'],
                ['key' => EventMeta::EVENT_END,   'value' => $date, 'compare' => '>=', 'type' => 'DATE'],
            ],
            $scope::TOMORROW => [
                ['key' => EventMeta::EVENT_START, 'value' => $now->modify('+1 day')->format('Y-m-d'), 'compare' => '=', 'type' => 'DATE'],
            ],
            $scope::WEEK => [
                ['key' => EventMeta::EVENT_START, 'value' => [
                    $date,
                    $now->modify('+7 days')->format('Y-m-d')
                ],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ],
            ],
            $scope::THIS_MONTH    => [
                ['key' => EventMeta::EVENT_START, 'value' => [
                    $now->modify('first day of this month')->format('Y-m-d'),
                    $now->modify('last day of this month')->format('Y-m-d')
                ],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ],
            ],
            $scope::NEXT_MONTH => [
                ['key' => EventMeta::EVENT_START, 'value' => [
                    $now->modify('first day of next month')->format('Y-m-d'),
                    $now->modify('last day of next month')->format('Y-m-d')
                ],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ],
            ],
            $scope::YEAR     => [
                ['key' => EventMeta::EVENT_START, 'value' => [
                    $now->modify('first day of January')->format('Y-m-d'),
                    $now->modify('last day of December')->format('Y-m-d')
                ],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ],
            ],
            default    => [['key' => $field, 'value' => $dateTime, 'compare' => '>', 'type' => 'DATETIME']]
        };
    }
}
