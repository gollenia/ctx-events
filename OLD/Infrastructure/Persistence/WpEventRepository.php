<?php

namespace Contexis\Events\Infrastructure\Persistence;

use WP_Query;
use Contexis\Events\Domain\Collections\EventCollection;
use Contexis\Events\Domain\Models\Event;
use Contexis\Events\Infrastructure\Persistence\AbstractPostTypeRepository;
use Contexis\Events\Infrastructure\PostTypes\EventPost;

class WpEventRepository extends AbstractPostTypeRepository {

	protected const POST_TYPE_CLASS = \Contexis\Events\Infrastructure\PostTypes\EventPost::class;
	protected const MODEL_CLASS = \Contexis\Events\Domain\Models\Event::class;
	protected const COLLECTION_CLASS = \Contexis\Events\Domain\Collections\EventCollection::class;

	public function query(?WP_Query $query = null, array $args = []) : EventCollection {
		$queryArgs = self::get_query_args($args);
    
		if (!$query) {
			$query = new WP_Query($queryArgs);
		} else {
			foreach ($queryArgs as $key => $value) {
				$query->set($key, $value);
			}
			$query->query($query->query_vars); // Query erneut ausführen
    	}
		
		$ret = \array_map(fn($post) => Event::find_by_post($post), $query->posts);

		$instance = new self();
		$instance->events = $ret;
		return $instance;
	}

	public function find(array $args = []) : EventCollection {
		return $this->query(null, $args);
	}

	public static function find_posts(?array $args = []) : array {
		
		$queryArgs = self::get_query_args($args);
		$query = new WP_Query($queryArgs);
		
		if(!$query->have_posts()) {
			return [];
		}
		return $query->posts;
	}

	public function get_query_args($args) { 
		
		$queryArgs = [
            'meta_query'     => ['relation' => 'AND'],
            'tax_query'      => [],
			'posts_per_page' => $args['limit'] ?? 100,
			'paged'		 => $args['paged'] ?? 0,
        ];

		if(empty($args['post_type'])) {
			$queryArgs['post_type'] = EventPost::POST_TYPE;
		}
		
		
		if(!empty($args['event-categories'])) {
			$queryArgs['tax_query'][] = [
				'taxonomy' => EventPost::CATEGORIES,
				'field'    => 'slug',
				'terms'    => $args['event-categories']
			];
		}

		if(!empty($args['tag'])) {
			$queryArgs['tax_query'][] = [
				'taxonomy' => 'event-tags',
				'field'    => 'term_id',
				'terms'    => $args['tag']
			];
		}

		if(!empty($args['speaker'])) {
			$queryArgs['meta_query'][] = [
				'key'     => '_speaker_id',
				'value'   => $args['speaker']
			];
		}

		if(!empty($args['status'])) {
			$queryArgs['post_status'] = $args['status'];
		}

		if (!empty($args['scope'])) {
            $queryArgs['meta_query'][] = self::get_date_scope_query($args['scope']);
        }

		if (!empty($args['location'])) {
            $queryArgs['meta_query'][] = [
                'key'     => '_location_id',
                'value'   => $args['location']
            ];
        }
		
		if (!empty($args['bookings'])) {
			$queryArgs['meta_query'][] = [
				'key'     => '_event_rsvp',
				'value'   => '1',
				'compare' => "="
			];
		}

		if (!empty($args['search'])) {
			$queryArgs['s'] = $args['search'];
		}

		if (!empty($args['exclude'])) {
			$queryArgs['post__not_in'] = $args['exclude'];
		}

		if (!empty($args['recurrence'])) {
			$queryArgs['meta_query'][] = [
				'key'     => '_recurrence_interval',
				'value'   => '0',
				'compare' => '>'
			];
		}

		
		if(!empty($args['orderby'])) {
			$queryArgs['orderby'] = 'meta_value';
			switch ($args['orderby']) {
				case 'date-time':
					$queryArgs['meta_key'] = '_event_start';
					break;
				case 'booking-date':
					$queryArgs['meta_key'] = '_event_rsvp_end';
					break;
				case 'booking':
					$queryArgs['meta_key'] = '_event_rsvp';
					break;
				case 'location':
					$queryArgs['meta_key'] = '_location_id';
					break;
				default:
					$queryArgs['orderby'] = $args['orderby'];
			}
		}

		if(!empty($args['order'])) {
			$queryArgs['order'] = $args['order'];
		}

		if(!empty($args['limit'])) {
			$queryArgs['posts_per_page'] = $args['limit'];
		}

		if(!empty($args['offset'])) {
			$queryArgs['offset'] = $args['offset'];
		}
		
		return $queryArgs;
	
		
	}

	private function get_date_scope_query(string $scope) : array {
		$now = new \DateTime('now', wp_timezone());
		$dateFormat = 'Y-m-d H:i:s';
		$field = get_option('dbem_events_current_are_past') ? '_event_end' : '_event_start';

		switch ($scope) {
			case 'past':
				return [
					['key' => $field, 'value' => $now->format($dateFormat), 'compare' => '<', 'type' => 'DATETIME']
				];

			case 'future':
				return [
	
					['key' => $field, 'value' => $now->format($dateFormat), 'compare' => '>', 'type' => 'DATETIME']
				];

			case 'today':
				return [
	
					['key' => '_event_start', 'value' => $now->format('Y-m-d'), 'compare' => '<=', 'type' => 'DATE'],
					['key' => '_event_end', 'value' => $now->format('Y-m-d'), 'compare' => '>=', 'type' => 'DATE']
				];

			case 'tomorrow':
				return [
				
					['key' => '_event_start', 'value' => (clone $now)->modify('+1 day')->format('Y-m-d'), 'compare' => '=', 'type' => 'DATE']
				];

			case 'week':
				return [
	
					['key' => '_event_start', 'value' => [$now->format('Y-m-d'), (clone $now)->modify('+7 days')->format('Y-m-d')], 'compare' => 'BETWEEN', 'type' => 'DATE']
				];

			case 'month':
				return [
					['key' => '_event_start', 'value' => [(clone $now)->modify('first day of this month')->format('Y-m-d'), (clone $now)->modify('last day of this month')->format('Y-m-d')], 'compare' => 'BETWEEN', 'type' => 'DATE']
				];

			case 'next-month':
				return [
					['key' => '_event_start', 'value' => [(clone $now)->modify('first day of next month')->format('Y-m-d'), (clone $now)->modify('last day of next month')->format('Y-m-d')], 'compare' => 'BETWEEN', 'type' => 'DATE']
				];

			case 'year':
				return [
					['key' => '_event_start', 'value' => [(clone $now)->modify('first day of January')->format('Y-m-d'), (clone $now)->modify('last day of December')->format('Y-m-d')], 'compare' => 'BETWEEN', 'type' => 'DATE']
				];

			default:
				if (preg_match('/^\d{4}-\d{2}-\d{2},\d{4}-\d{2}-\d{2}$/', $scope)) {
					$dates = explode(',', $scope);
					return [
						['key' => '_event_start', 'value' => $dates, 'compare' => 'BETWEEN', 'type' => 'DATE']
					];
				} 
				return [];
				
    	}
	}
}