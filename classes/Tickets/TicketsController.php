<?php

namespace Contexis\Events\Tickets;

use EM_Event;
use EM_Gateway;
use WP_REST_Response;
use WP_REST_Server;

class TicketsController {

	private array $allowed_fields = [
		'event_id',
		'ticket_name',
		'ticket_description',
		'ticket_min',
		'ticket_max',
		'ticket_price',
		'ticket_spaces',
		'ticket_required',
		'ticket_order',
		'ticket_meta',
	];

	public static function init() {
		$instance = new self();
		add_action('rest_api_init', array($instance, 'register_rest_route') );
	}

	/**
	 * Register the REST API route with CRUD methods
	 *
	 * @return void
	 */
	public function register_rest_route() {
		register_rest_route( 'events/v2', '/ticket(?:/(?P<id>\d+))?', [
			['methods' => WP_REST_Server::READABLE, 'callback' => [$this, 'read_ticket'], 'permission_callback' => function ( \WP_REST_Request $request ) {
                return true;
            },],
			['methods' => WP_REST_Server::CREATABLE, 'callback' => [$this, 'create_ticket'], 'permission_callback' => function ( \WP_REST_Request $request ) {
                return true;
            },],
			['methods' => WP_REST_Server::DELETABLE, 'callback' => [$this, 'delete_ticket'], 'permission_callback' => function ( \WP_REST_Request $request ) {
                return true;
            },],
			['methods' => WP_REST_Server::EDITABLE, 'callback' => [$this, 'update_ticket'], 'permission_callback' => function ( \WP_REST_Request $request ) {
                return true;
            }, 'login_user_id' => get_current_user_id()],
		], true);

		register_rest_route( 'events/v2', '/tickets', [
			['methods' => WP_REST_Server::READABLE, 'callback' => [$this, 'read_tickets'], 'permission_callback' => function ( \WP_REST_Request $request ) {
				return true;
			},],
		], true);
	}

	/**
	 * Create a new ticket
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function create_ticket($request) {
		$ticket = new Ticket();

		$event = \EM_Event::find_by_post_id($request->get_param('post_id'), 'post_id');
		$data = $request->get_params();

		$ticket->event_id = $event->event_id;
		
		foreach($data as $key => $value) {
			if(!in_array($key, $this->allowed_fields)) {
				continue;
			}
			$ticket->$key = $value;
		}

		$ticket->ticket_meta = [
			'primary' => 0,
		];
		//$ticket->compat_keys();
		$result = $ticket->save();

		$response = new WP_REST_Response(['ticket' => $ticket, 'request' => $request, 'errors' => $ticket->errors]);
		$response->set_status($result ? 200 : 400);

		return $response;
	}

	/**
	 * Update an existing booking
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function update_ticket(\WP_REST_Request $request) {
		$id = $request->get_param('ticket_id');
		$ticket = new \Contexis\Events\Tickets\Ticket($id);
		$data = $request->get_params();
		
		foreach($data as $key => $value) {
			if(!in_array($key, $this->allowed_fields)) {
				continue;
			}
			$ticket->$key = $value;
		}

		$success = $ticket->save();
		return new WP_REST_Response($ticket->errors, $ticket->errors ? 400 : 200);
	}

	public function can_get_bookings() {
		if(!is_user_logged_in()) return false;
		return current_user_can('manage_others_bookings');
	}

	
	/**
	 * Delete a booking by its ID
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function delete_ticket($request) {
		$id = $request->get_param('id');
		$ticket = new Ticket($id);
		$result = $ticket->delete();

		$response = new WP_REST_Response(['success' => $result]);
		$response->set_status($result ? 200 : 400);	 
		return new WP_REST_Response(true);
	}

	function get_tickets_permissions_check($request) {
		return true;
	}

	function read_tickets($request) {
		
		$event = \EM_Event::find_by_post_id($request->get_param('post_id'), 'post_id');
		
		$ticket_data = \Contexis\Events\Tickets\Tickets::find_by_event_id($event->event_id);
		
		
		$tickets = [];

		foreach( $ticket_data->tickets as $ticket ) {
			$tickets[] = $ticket->get_rest_data();
		}
		return $tickets;
	}
}

TicketsController::init();