<?php

namespace Contexis\Events\Infrastructure\Persistence\Query;

use Contexis\Events\Application\Query\ListEventsQuery;
use Contexis\Events\Application\Requests\EventPageRequest;
use DateTimeImmutable;
use Contexis\Events\Domain\Contracts\EventCriteria;
use Contexis\Events\Domain\ValueObjects\EventScope;
use Contexis\Events\Infrastructure\PostTypes\EventPost;
use Contexis\Events\Core\Contracts\QueryOptions;
use Contexis\Events\Core\Contracts\Criteria;
use Contexis\Events\Core\Contracts\QueryBuilder;
use Contexis\Events\Core\Contracts\QueryRequest;

final class WpEventQueryBuilder extends AbstractWpQueryBuilder
{

    protected function build(QueryRequest $request): void
	{
		if(!$request instanceof EventPageRequest) {
			throw new \InvalidArgumentException('Expected instance of EventPageRequest');
		}
    
        $this->args = [
            'post_type'      => EventPost::POST_TYPE,
            'posts_per_page' => $request->perPage,
            'paged'          => max(1, $request->page),
            'meta_query'     => ['relation' => 'AND'],
            'tax_query'      => [],
            'fields'         => 'all',
        ];


        if ($request->categories) {
            $this->args['tax_query'][] = [
                'taxonomy' => EventPost::CATEGORIES,
                'field'    => 'slug',
                'terms'    => $request->categories,
            ];
        }

        if ($request->tags) {
            $this->args['tax_query'][] = [
                'taxonomy' => EventPost::TAGS,
                'field'    => 'term_id',
                'terms'    => $request->tags,
            ];
        }

        foreach ($request->persons as $personId) {
            $this->args['meta_query'][] = ['key' => '_person_id', 'value' => (string)$personId];
        }

        if ($request->location) {
            $this->args['meta_query'][] = ['key' => '_location_id', 'value' => (string)$request->location];
        }

        if ($request->bookable) {
            $this->args['meta_query'][] = ['key' => '_booking_enabled', 'value' => '1', 'compare' => '='];
        }

        if ($request->scope) {
            foreach (self::dateScopeToMetaQuery($request->scope) as $cond) {
                $this->args['meta_query'][] = $cond;
            }
        }

        // Order / OrderBy Mapping
        [$orderby, $metaKey] = self::mapOrderBy($request->orderBy);
        $this->args['order'] = strtoupper($request->order) === 'ASC' ? 'ASC' : 'DESC';
        if ($metaKey) {
            $this->args['orderby'] = 'meta_value';
            $this->args['meta_key'] = $metaKey;
        } else {
            $this->args['orderby'] = $orderby; // 'title', 'date', ...
        }

    }

    private static function mapOrderBy(string $orderBy): array
    {
        return match ($orderBy) {
            'date-time'     => ['meta_value', '_event_start'],
            'booking-date'  => ['meta_value', '_event_rsvp_end'],
            'booking'       => ['meta_value', '_event_rsvp'],
            'location'      => ['meta_value', '_location_id'],
            default         => [$orderBy, null], // z.B. 'title'|'date'|'menu_order'
        };
    }

    private static function dateScopeToMetaQuery(EventScope $scope): array
    {
        $now = new DateTimeImmutable('now', wp_timezone());
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
