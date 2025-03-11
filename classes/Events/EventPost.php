<?php

namespace Contexis\Events\Events;

use Contexis\Events\Collections\EventCollection;
use \Contexis\Events\Models\Event;
use Events;
use WP_Query;

/**
 * Controls how events are queried and displayed via the WordPress Custom Post APIs
 * @author marcus
 *
 */
class EventPost {

	const POST_TYPE = "event";
	
	public static function init(){

		$instance = new self;
		add_action('init', array($instance, 'register_post_type'));
		add_action('parse_query', array($instance,'parse_query'));
		add_action('rest_api_init', array($instance, 'register_meta') );
		//add_filter('rest_prepare_event', array('EventRestController', 'prepare_event'), 10, 3);		
	}	


	/**
	 * Returns price and free spaces for a given event
	 *
	 * @param [type] $request
	 * @return void
	 */
	public function get_rest_bookinginfo($request) {
		$result = [
			'success' => false,
		];

		$id = $request->get_param('id');
		if(!$id) return $result;
		
		$event = Event::find_by_post_id($id);
		
		$result['success'] = true;

		$data = [
			'price_float' => $event->get_price(),
			'formatted_price' => $event->get_formatted_price(),
			'available_spaces' => $event->get_bookings()->get_available_spaces(),
			'booked_spaces' => $event->get_bookings()->get_booked_spaces(),
		];

		$result['data'] = $data;

		return $result;
	}

	function prepare_event($response, $post, $request) {
		$event = Event::find_by_post($post);
		
		if (!$event->event_exists()) {
			return new \WP_REST_Response([
				'error' => __('Event not found', 'events')
			], 404);
		}
	
		// Basisdaten des WordPress-Posts holen
		$post_data = $response->get_data();
	
		// Custom-Felder aus dem Event-Objekt holen
		$event_data = $event->get_rest_fields();
	
		// Angeforderte Felder ermitteln
		$requested_fields = $request->get_param('_fields');
		$requested_fields = is_array($requested_fields) ? $requested_fields : explode(',', (string) $requested_fields);
		$requested_fields = array_filter($requested_fields);
	
		// Falls _fields gesetzt wurde, nur diese Felder zurückgeben
		if (!empty($requested_fields)) {
			$post_data = array_intersect_key($post_data, array_flip($requested_fields));
			$event_data = array_intersect_key($event_data, array_flip($requested_fields));
		}
	
		// Beide Arrays zusammenführen (Post + Event-spezifische Daten)
		$data = array_merge($post_data, $event_data);
	
		// Zusätzliche Verknüpfungen laden, falls angefordert
		if (in_array('location', $requested_fields)) {
			$data['location'] = $event->get_location()->get_rest_fields();
		}
	
		if (in_array('speaker', $requested_fields)) {
			$data['speaker'] = \Contexis\Events\Speaker::get($event->speaker_id)->get_rest_fields();
		}
	
		if (in_array('tags', $requested_fields)) {
			$data['tags'] = $event->get_taxonomies()->get_rest_fields();
		}
	
		if (in_array('tickets', $requested_fields)) {
			$data['tickets'] = $event->get_bookings()->get_available_tickets()->get_rest_fields();
		}
	
		return rest_ensure_response($data);
	}

	public function register_post_type() {
		$labels = [
			'name' => __('Events','events'),
			'singular_name' => __('Event','events'),
			'menu_name' => __('Events','events'),
			'add_new_item' => __('Add New Event','events'),
			'edit' => __('Edit','events'),
			'edit_item' => __('Edit Event','events'),
			'view' => __('View','events'),
			'view_item' => __('View Event','events'),
			'search_items' => __('Search Events','events'),
			'not_found' => __('No Events Found','events'),
			'not_found_in_trash' => __('No Events Found in Trash','events'),
			'parent' => __('Parent Event','events'),
		];

		$event_post_type = [	
			'public' => true,
			'hierarchical' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
			'show_in_nav_menus'=>true,
			'can_export' => true,
			'exclude_from_search' => !get_option('dbem_cp_events_search_results'),
			'publicly_queryable' => true,
			'rewrite' => ['slug' => EM_POST_TYPE_EVENT_SLUG,'with_front'=>false],
			'has_archive' => false,
			'supports' => ['title','editor','excerpt','thumbnail','author','custom-fields'],
			'template' => [
					['ctx-blocks/grid-row', [], [
						['ctx-blocks/grid-column', ['widthLarge' => 2], [['core/paragraph', ['placeholder' => 'Event-Beschreibung']],]],
						['ctx-blocks/grid-column', ['widthLarge' => 1], [
							['events-manager/details', []],
						]]
					]],
					['core/separator'],
					['core/group', ['layout' => ['type' => 'flex', 'flexWrap' => 'nowrap', 'justifyContent' => 'right']], [['events-manager/booking', ['title' => 'Anmeldung']]]]
			],
			'capability_type' => 'event',
			'capabilities' => [
				'publish_posts' => 'publish_events',
				'edit_posts' => 'edit_events',
				'edit_others_posts' => 'edit_others_events',
				'delete_posts' => 'delete_events',
				'delete_others_posts' => 'delete_others_events',
				'read_private_posts' => 'read_private_events',
				'edit_post' => 'edit_event',
				'delete_post' => 'delete_event',
				'read_post' => 'read_event',		
			],
			'label' => __('Events','events'),
			'description' => __('Display events on your blog.','events'),
			'labels' => $labels,
			//'rest_controller_class' => '\Contexis\Events\Events\EventRestController',
			'menu_icon' => 'dashicons-calendar-alt'
		];
		
		register_post_type( 'event', $event_post_type );
	}

	public function register_meta() {

		$metadata = json_decode(file_get_contents(__DIR__ . '/metadata.json'), true);

		foreach($metadata as $meta){
			if(!in_array('event', $meta['post_type'])){
				continue;
			}
			register_post_meta( 'event', $meta['name'], [
				'type' => $meta['type'],
				'single'       => true,
				
				'sanitize_callback' => '',
				'auth_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
				'show_in_rest' => [
					'schema' => [	
						'style' => $meta['type']
					]
				]
			]);
		}

		foreach($metadata as $meta){
			if(!in_array('event-recurring', $meta['post_type'])){
				continue;
			}
			register_post_meta( 'event-recurring', $meta['name'], [
				'type' => $meta['type'],
				'single'       => true,
				'sanitize_callback' => '',
				'auth_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
				'show_in_rest' => [
					'schema' => [
						'style' => $meta['type']
					]
				]
			]);
		}
	

	}
	
	public static function parse_query(WP_Query $query) : WP_Query{
		if(!isset($query->query_vars['post_type'])){
			return $query;
		}
		if( $query->query_vars['post_type'] != 'event' && $query->query_vars['post_type'] != 'event-recurring' ){
			return $query;
		}
		
		if( !$query->is_main_query() ){
			return $query;
		}
		$args = [];
		$args['scope'] = (!empty($_REQUEST['scope'])) ? $_REQUEST['scope']:'future';
		if(!empty($_REQUEST['orderby'])) $args['orderby'] = $_REQUEST['orderby'];
		if(!empty($_REQUEST['order'])) $args['order'] = $_REQUEST['order'];
		if(!empty($_REQUEST['event-categories'])) $args['event-categories'] = $_REQUEST['event-categories'];
		

		$args = EventCollection::get_query_args($args);
		$query->query_vars = array_merge($query->query_vars, $args);
		\Contexis\Events\Utilities::object_to_js_console($query->query_vars);
		return $query;
		
	}

	public function get_attendees($booking) {
		$result = [];
		$tickets = $booking->booking_meta['attendees'];
		foreach($tickets as $key => $ticket) {
			foreach ($ticket as $attendee) {
				$result[] = [ 'id' => $key, 'fields' => $attendee ];
			}
		}
		return $result;
	}

}
EventPost::init();