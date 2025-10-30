<?php

use Contexis\Events\Core\Utilities\EventScope;
use Contexis\Events\Domain\Contracts\EventCriteria;
use Contexis\Events\Domain\ValueObjects\EventScope;

final class WpEventQuery
{
    public function build(EventCriteria $c): array
    {
        $args = [
            'post_type'      => EventPost::POST_TYPE,
            'posts_per_page' => $c->limit,
            'paged'          => max(1, $c->page),
            'meta_query'     => ['relation' => 'AND'],
            'tax_query'      => [],
            'fields'         => 'all',
        ];

        if ($c->offset !== null) $args['offset'] = $c->offset;
        if ($c->search) $args['s'] = $c->search;
        if ($c->excludeIds) $args['post__not_in'] = $c->excludeIds;
        if ($c->statuses) $args['post_status'] = $c->statuses;

        if ($c->categories) {
            $args['tax_query'][] = [
                'taxonomy' => EventPost::CATEGORIES,
                'field'    => 'slug',
                'terms'    => $c->categories,
            ];
        }

        if ($c->tags) {
            $args['tax_query'][] = [
                'taxonomy' => 'event-tags',
                'field'    => 'term_id',
                'terms'    => $c->tags,
            ];
        }

        foreach ($c->speakers as $speakerId) {
            $args['meta_query'][] = ['key' => '_speaker_id', 'value' => (string)$speakerId];
        }

        if ($c->locationId) {
            $args['meta_query'][] = ['key' => '_location_id', 'value' => (string)$c->locationId];
        }

        if ($c->bookableOnly) {
            $args['meta_query'][] = ['key' => '_event_rsvp', 'value' => '1', 'compare' => '='];
        }

        if ($c->recurringOnly) {
            $args['meta_query'][] = ['key' => '_recurrence_interval', 'value' => '0', 'compare' => '>'];
        }

        if ($c->scope) {
            foreach ($this->dateScopeToMetaQuery($c->scope) as $cond) {
                $args['meta_query'][] = $cond;
            }
        }

        // Order / OrderBy Mapping
        [$orderby, $metaKey] = $this->mapOrderBy($c->orderBy);
        $args['order'] = strtoupper($c->order) === 'ASC' ? 'ASC' : 'DESC';
        if ($metaKey) {
            $args['orderby'] = 'meta_value';
            $args['meta_key'] = $metaKey;
        } else {
            $args['orderby'] = $orderby; // 'title', 'date', ...
        }

        return $args;
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
                ['key' => '_event_start', 'value' => [$date, $now->modify('+7 days')->format('Y-m-d')], 'compare' => 'BETWEEN', 'type' => 'DATE'],
            ],
            $scope::THIS_MONTH    => [
                ['key' => '_event_start', 'value' => [$now->modify('first day of this month')->format('Y-m-d'), $now->modify('last day of this month')->format('Y-m-d')], 'compare' => 'BETWEEN', 'type' => 'DATE'],
            ],
            $scope::NEXT_MONTH => [
                ['key' => '_event_start', 'value' => [$now->modify('first day of next month')->format('Y-m-d'), $now->modify('last day of next month')->format('Y-m-d')], 'compare' => 'BETWEEN', 'type' => 'DATE'],
            ],
            $scope::YEAR     => [
                ['key' => '_event_start', 'value' => [$now->modify('first day of January')->format('Y-m-d'), $now->modify('last day of December')->format('Y-m-d')], 'compare' => 'BETWEEN', 'type' => 'DATE'],
            ],
            default    => [['key' => $field, 'value' => $dateTime, 'compare' => '>', 'type' => 'DATETIME']]
        };
    }
}