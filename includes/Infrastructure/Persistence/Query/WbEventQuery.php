<?php

namespace Contexis\Events\Infrastructure\Persistence\Query;

use DateTimeImmutable;
use Contexis\Events\Domain\Contracts\EventCriteria;
use Contexis\Events\Domain\ValueObjects\EventScope;
use Contexis\Events\Infrastructure\PostTypes\EventPost;
use Contexis\Events\Core\Contracts\QueryOptions;
use Contexis\Events\Core\Contracts\Criteria;

final class WpEventQuery extends AbstractWpQuery
{
    public function build(Criteria $criteria): QueryOptions
    {
        $c = $criteria;
        $this->query = [
            'post_type'      => EventPost::POST_TYPE,
            'posts_per_page' => $c->limit,
            'paged'          => max(1, $c->page),
            'meta_query'     => ['relation' => 'AND'],
            'tax_query'      => [],
            'fields'         => 'all',
        ];


        if ($c->categories) {
            $this->query['tax_query'][] = [
                'taxonomy' => EventPost::CATEGORIES,
                'field'    => 'slug',
                'terms'    => $c->categories,
            ];
        }

        if ($c->tags) {
            $this->query['tax_query'][] = [
                'taxonomy' => EventPost::TAGS,
                'field'    => 'term_id',
                'terms'    => $c->tags,
            ];
        }

        foreach ($c->persons as $personId) {
            $this->query['meta_query'][] = ['key' => '_person_id', 'value' => (string)$personId];
        }

        if ($c->locationId) {
            $this->query['meta_query'][] = ['key' => '_location_id', 'value' => (string)$c->locationId];
        }

        if ($c->bookableOnly) {
            $this->query['meta_query'][] = ['key' => '_event_rsvp', 'value' => '1', 'compare' => '='];
        }

        if ($c->scope) {
            foreach ($this->dateScopeToMetaQuery($c->scope) as $cond) {
                $this->query['meta_query'][] = $cond;
            }
        }

        // Order / OrderBy Mapping
        [$orderby, $metaKey] = $this->mapOrderBy($c->orderBy);
        $this->query['order'] = strtoupper($c->order) === 'ASC' ? 'ASC' : 'DESC';
        if ($metaKey) {
            $this->query['orderby'] = 'meta_value';
            $this->query['meta_key'] = $metaKey;
        } else {
            $this->query['orderby'] = $orderby; // 'title', 'date', ...
        }

        return $this;
    }

    private function mapOrderBy(string $orderBy): array
    {
        return match ($orderBy) {
            'date-time'     => ['meta_value', '_event_start'],
            'booking-date'  => ['meta_value', '_event_rsvp_end'],
            'booking'       => ['meta_value', '_event_rsvp'],
            'location'      => ['meta_value', '_location_id'],
            default         => [$orderBy, null], // z.B. 'title'|'date'|'menu_order'
        };
    }

    private function dateScopeToMetaQuery(EventScope $scope): array
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
