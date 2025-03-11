<?php

namespace Contexis\Events\Collections;
use WP_Query;
use WP_Post;
use Countable;
use IteratorAggregate;
use Contexis\Events\Models\Event;


class EventCollection implements Countable, IteratorAggregate {
	
	/** @var Event[] */
	protected array $events = [];

	public static function find($args = []) {
		return self::query(null, $args);
	}

	public static function query(?WP_Query $query = null, array $args = []) : EventCollection {
		$queryArgs = self::get_query_args($args);
    
		if (!$query) {
			var_dump("Having a new query");
			$query = new WP_Query($queryArgs);
		} else {
			foreach ($queryArgs as $key => $value) {
				$query->set($key, $value);
				var_dump($key, $value);
			}
			$query->query($query->query_vars); // Query erneut ausführen
    	}
		
		$ret = array_map(fn($post) => Event::find_by_post($post), $query->posts);

		$instance = new self();
		$instance->events = $ret;
		return $instance;
	}

	public static function find_posts(?array $args = []) : array {
		
		$queryArgs = self::get_query_args($args);
		$query = new WP_Query($queryArgs);
		
		if(!$query->have_posts()) {
			return [];
		}
		return $query->posts;
	}

	public static function get_query_args($args) { 
		
		$queryArgs = [
            'meta_query'     => ['relation' => 'AND'],
            'tax_query'      => [],
			'posts_per_page' => $args['limit'] ?: 100,
			'paged'		 => $args['paged'] ?: 0,
        ];

		if(empty($args['post_type'])) {
			$queryArgs['post_type'] = 'event';
		}
		
		
		if(!empty($args['event-categories'])) {
			$queryArgs['tax_query'][] = [
				'taxonomy' => 'event-categories',
				'field'    => 'term_id',
				'terms'    => intval($args['event-categories'])
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
					$queryArgs['meta_key'] = '_event_start_date';
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

	private static function get_date_scope_query(string $scope) : array {
		$now = new \DateTime('now', wp_timezone());
		$dateFormat = 'Y-m-d H:i:s';
		$field = get_option('dbem_events_current_are_past') ? '_event_end_date' : '_event_start_date';

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
	
					['key' => '_event_start_date', 'value' => $now->format('Y-m-d'), 'compare' => '<=', 'type' => 'DATE'],
					['key' => '_event_end_date', 'value' => $now->format('Y-m-d'), 'compare' => '>=', 'type' => 'DATE']
				];

			case 'tomorrow':
				return [
				
					['key' => '_event_start_date', 'value' => (clone $now)->modify('+1 day')->format('Y-m-d'), 'compare' => '=', 'type' => 'DATE']
				];

			case 'week':
				return [
	
					['key' => '_event_start_date', 'value' => [$now->format('Y-m-d'), (clone $now)->modify('+7 days')->format('Y-m-d')], 'compare' => 'BETWEEN', 'type' => 'DATE']
				];

			case 'month':
				return [
					['key' => '_event_start_date', 'value' => [(clone $now)->modify('first day of this month')->format('Y-m-d'), (clone $now)->modify('last day of this month')->format('Y-m-d')], 'compare' => 'BETWEEN', 'type' => 'DATE']
				];

			case 'next-month':
				return [
					['key' => '_event_start_date', 'value' => [(clone $now)->modify('first day of next month')->format('Y-m-d'), (clone $now)->modify('last day of next month')->format('Y-m-d')], 'compare' => 'BETWEEN', 'type' => 'DATE']
				];

			case 'year':
				return [
					['key' => '_event_start_date', 'value' => [(clone $now)->modify('first day of January')->format('Y-m-d'), (clone $now)->modify('last day of December')->format('Y-m-d')], 'compare' => 'BETWEEN', 'type' => 'DATE']
				];

			default:
				if (preg_match('/^\d{4}-\d{2}-\d{2},\d{4}-\d{2}-\d{2}$/', $scope)) {
					$dates = explode(',', $scope);
					return [
						['key' => '_event_start_date', 'value' => $dates, 'compare' => 'BETWEEN', 'type' => 'DATE']
					];
				} 
				return [];
				
    	}
	}

	public function count( ) : int {
		return count($this->events);
	}
	
	public function getIterator(): \Traversable {
        return new \ArrayIterator($this->events);
    }

	
}
?>