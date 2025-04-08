<?php

namespace Contexis\Events\Events;

use Contexis\Events\Collections\BookingCollection;
use Contexis\Events\Collections\EventCollection;
use \Contexis\Events\Models\Event;
use Contexis\Events\Models\Speaker;

class EventController 
{

	public static function init() {
		$instance = new self();
		add_action( 'rest_api_init', [$instance, 'register_routes'], 10 );

	}

	public function register_routes() {
		
        register_rest_route( '/events/v2', '/events', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
            ),
        ) );
        register_rest_route( '/events/v2', '/event(?:/(?P<id>\d+))?', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'get_item' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
            ),
        ) );
    }

	public function get_items_permissions_check( $request ) {
        return true;
    }

	public function get_item_permissions_check( $request ) {
		return true;
	}

	public function get_items( $request ) {
      
      
		$posts = EventCollection::find_posts($request->get_params());

        $data = array();

        if ( empty( $posts ) ) {
            return rest_ensure_response( $data );
        }

        foreach ( $posts as $post ) {
            $response = $this->prepare_item_for_response( $post, $request );
            $data[] = $this->prepare_response_for_collection( $response );
        }

        // Return all of our comment response data.
        return rest_ensure_response( $data );
    }

	public function get_item( $request ) {
        $id = (int) $request['id'];
        $post = get_post( $id );

        if ( empty( $post ) ) {
            return rest_ensure_response( array() );
        }

        $response = $this->prepare_item_for_response( $post, $request );
		$test = rest_ensure_response( $response );
		
        // Return all of our post response data.
        return $test;
    }

	public function prepare_item_for_response( $post, $request ) {
        $event = Event::find_by_post($post);
		if(!$event->event_id) {
			return new \WP_REST_Response([
				'error' => __('Event not found', 'events')
			], 404);
		}
		
		
		$requested_fields = $request->get_param('fields');
		$requested_fields = is_array($requested_fields) ? $requested_fields : explode(',', (string) $requested_fields);
		$requested_fields = array_filter($requested_fields);
		
		if(in_array('event', $requested_fields) || empty($requested_fields)) {
			$data = $event->get_rest_fields();
		}
		
		if (in_array('location', $requested_fields)) {
			$data['location'] = $event->get_location($event)->get_rest_fields();
		}
		
		if(in_array('speaker', $requested_fields)) {
			$data['speaker'] = Speaker::get($event->speaker_id)->get_rest_fields();
		}

		if(in_array('tickets', $requested_fields)) {
			$data['tickets_available'] = $event->get_bookings()->get_available_tickets()->get_rest_fields();
		}

		if(in_array('categories', $requested_fields)) {
			$data['categories'] = $event->get_categories();
		}

		if(in_array('gateways', $requested_fields)) {
			$data['gateways_available'] = \EM_Gateways::active_gateways();
		}

		if(in_array('forms', $requested_fields)) {
			$data['forms'] = [
				'registration_fields' => \EM_Booking_Form::get_booking_form($event->post_id),
		    	'attendee_fields' => \EM_Attendees_Form::get_attendee_form($event->post_id),
			];
		}

        return $data ;
    }

	public function prepare_response_for_collection( $response ) {
        if ( ! ( $response instanceof \WP_REST_Response ) ) {
            return $response;
        }

        $data = (array) $response->get_data();
        $server = rest_get_server();

        if ( method_exists( $server, 'get_compact_response_links' ) ) {
            $links = call_user_func( array( $server, 'get_compact_response_links' ), $response );
        } else {
            $links = call_user_func( array( $server, 'get_response_links' ), $response );
        }

        if ( ! empty( $links ) ) {
            $data['_links'] = $links;
        }

        return $data;
    }

	
	public function authorization_status_code() {

        $status = 401;

        if ( is_user_logged_in() ) {
            $status = 403;
        }

        return $status;
    }

	public static function read_event(\WP_REST_Request $request) {
		$id = $request->get_param('id', null);
		if(!$id) {
			return new \WP_REST_Response([
				'error' => __('No event ID given', 'events')
			], 400);
		}
		$event = Event::find_by_post_id($id);
		if(!$event) {
			return new \WP_REST_Response([
				'error' => __('Event not found', 'events')
			], 404);
		}
		$data = self::prepare_event($event);
		return new \WP_REST_Response($data, 200);
	}

	public static function read_events($args = []) {
		$result = [];
		if(empty($args)) {
			global $post;
			$args = ['post_id' => $post->id];
		}
		$data = EventCollection::find($args);
		if (!$data) return $result;
		foreach($data as $event) {
			$result[] = self::prepare_event($event);
		}
		return $result;
	}

	private static function get_location($event) {
		$location = $event->get_location();
		return [
			'location_id' => $location->post_id, 
			'address' => $location->location_address,
			'zip' => $location->location_postcode,
			'city' => $location->location_town,
			'name' => $location->location_name,
			'url' => $location->location_url,
			'country' => $location->location_country,
			'state' => $location->location_state,
		];
	}

	public static function prepare_event($event) {

		$location = $event->get_location();
		$audience = get_post_meta($event->post_id, '_event_audience', true);
		$category = $event->get_categories()->get_first();
		
		$speaker = Speaker::get($event->speaker_id);
		$price = 0;
		$booking = BookingCollection::from_event($event);
		$tickets = $booking->get_tickets()->tickets;
		if(!empty($tickets)) {
			$first_ticket = key($tickets);
			$price = floatval($tickets[$first_ticket]->ticket_price);
		}
		return [
			[
				'bookings' => [
					'has_bookings' => $event->event_rsvp,
					'spaces' => $booking->get_available_spaces()
				],
				'ID' => $event->post_id,
				'event_id' => $event->event_id,
				'link' => get_permalink($event->post_id),
				'image' => $event->get_image(),
				'category' => $category ? [ 
					'id' => $category->id,
					'color' => $category->color, 
					'name' => $category->name,
					'slug' => $category->slug
				] : null,
				'location' => [ 
					'location_id' => $location->post_id, 
					'address' => $location->location_address,
					'zip' => $location->location_postcode,
					'city' => $location->location_town,
					'name' => $location->location_name,
					'url' => $location->location_url,
					'country' => $location->location_country,
					'state' => $location->location_state,
				],
				'has_coupons' => \EM_Coupons::event_has_coupons($event),
				'date' => \Contexis\Events\Intl\Date::get_date($event->start()->getTimestamp(), $event->end()->getTimestamp()),
				'time' => \Contexis\Events\Intl\Date::get_time($event->start()->getTimestamp(), $event->end()->getTimestamp()),
				'price' => new \Contexis\Events\Intl\Price($price),
				'is_free' => $event->is_free(),
				'start' => $event->start()->getTimestamp(),
				'end' => $event->end()->getTimestamp(),
				'single_day' => $event->event_start_date == $event->event_end_date,
				'audience' => $audience,
				'excerpt' => $event->post_excerpt,
				'title' => $event->event_name,
				'speaker' => $speaker,
				
				'allowDonation' => get_metadata('post', $event->post_id, '_event_rsvp_donation', true) == "1"
			]
		];
	}

}

EventController::init();