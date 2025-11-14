<?php

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Application\EventCriteria;
use Contexis\Events\Event\Application\EventPageCriteria;
use Contexis\Events\Event\Domain\TimeScope;
use Contexis\Events\Shared\Domain\ValueObjects\OrderBy;
use Contexis\Events\Shared\Infrastructure\Abstracts\WpQueryBuilder;

final class WpEventQueryBuilder extends WpQueryBuilder
{
    public static function fromCriteria(EventCriteria $criteria): self
    {
        $builder = new self()
            ->withPostType(EventPost::POST_TYPE)
            ->withPagination($criteria->page, $criteria->perPage)
            ->withStatus($criteria->status ?? ['publish'])
            ->withTaxonomy(EventPost::CATEGORIES, $criteria->categories)
            ->withTaxonomy(EventPost::TAGS, $criteria->tags)
        ;

        if ($criteria->location !== null) {
            $builder = $builder->withMetaEquals('_location_id', (string) $criteria->location);
        }

        if ($criteria->bookable) {
            $builder = $builder->withMetaEquals('_booking_enabled', '1');
        }

        if ($criteria->person != null) {
            $builder = $builder->withMetaEquals('_person_id', (string) $criteria->person);
        }

        if ($criteria->scope) {
            foreach (self::dateScopeToMetaQuery($criteria->scope) as $cond) {
                $builder->args['meta_query'][] = $cond;
            }
        }

        $orderBy = self::mapOrderBy($criteria->orderBy)->withOrder($criteria->order);
        $builder = $builder->orderBy($orderBy);

        return $builder;
    }


    private static function mapOrderBy(string $orderBy): OrderBy
    {
        return match ($orderBy) {
            'date-time'     => OrderBy::fromMeta('_event_start'),
            'booking-date'  => OrderBy::fromMeta('_event_rsvp_end'),
            'booking'       => OrderBy::fromMeta('_event_rsvp'),
            'location'      => OrderBy::fromMeta('_location_id'),
            'person'        => OrderBy::fromMeta('_person_id'),
            default         => OrderBy::fromField($orderBy),
        };
    }

    private static function dateScopeToMetaQuery(TimeScope $scope): array
    {
        $now = new \DateTimeImmutable('now', wp_timezone());
        $date = $now->format('Y-m-d');
        $dateTime = $now->format('Y-m-d H:i:s');
        $field = get_option('dbem_events_current_are_past') ? '_event_end' : '_event_start';

        return match ($scope) {
            $scope::PAST    => [['key' => $field, 'value' => $dateTime, 'compare' => '<', 'type' => 'DATETIME']],
            $scope::FUTURE  => [['key' => $field, 'value' => $dateTime, 'compare' => '>', 'type' => 'DATETIME']],
            $scope::TODAY   => [
                ['key' => '_event_start', 'value' => $date, 'compare' => '<=', 'type' => 'DATE'],
                ['key' => '_event_end',   'value' => $date, 'compare' => '>=', 'type' => 'DATE'],
            ],
            $scope::TOMORROW => [
                ['key' => '_event_start', 'value' => $now->modify('+1 day')->format('Y-m-d'), 'compare' => '=', 'type' => 'DATE'],
            ],
            $scope::WEEK => [
                ['key' => '_event_start', 'value' => [
                    $date,
                    $now->modify('+7 days')->format('Y-m-d')],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ],
            ],
            $scope::THIS_MONTH    => [
                ['key' => '_event_start', 'value' => [
                    $now->modify('first day of this month')->format('Y-m-d'),
                    $now->modify('last day of this month')->format('Y-m-d')],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ],
            ],
            $scope::NEXT_MONTH => [
                ['key' => '_event_start', 'value' => [
                    $now->modify('first day of next month')->format('Y-m-d'),
                    $now->modify('last day of next month')->format('Y-m-d')],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ],
            ],
            $scope::YEAR     => [
                ['key' => '_event_start', 'value' => [
                    $now->modify('first day of January')->format('Y-m-d'),
                    $now->modify('last day of December')->format('Y-m-d')],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ],
            ],
            default    => [['key' => $field, 'value' => $dateTime, 'compare' => '>', 'type' => 'DATETIME']]
        };
    }
}
