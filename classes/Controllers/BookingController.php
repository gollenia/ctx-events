<?php

namespace Contexis\Events\Controllers;

use Contexis\Events\Collections\CouponCollection;
use Contexis\Events\Collections\TicketCollection;
use Contexis\Events\Forms\AttendeesForm;
use Contexis\Events\Forms\BookingForm;
use Contexis\Events\Models\Booking;
use Contexis\Events\Models\BookingStatus;
use Contexis\Events\Models\Coupon;
use WP_REST_Response;
use WP_REST_Server;
use Contexis\Events\Models\Event;
use Contexis\Events\Payment\GatewayCollection;
use WP_REST_Request;
use Contexis\Events\Intl\Price;
use Contexis\Events\Collections\BookingCollection;


class BookingController {

	private array $action_to_status = [
		'approve' => BookingStatus::APPROVED,
		'reject' => BookingStatus::REJECTED,
		'unapprove' => BookingStatus::PENDING,
		'cancel' => BookingStatus::CANCELED
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
		register_rest_route( 'events/v2', '/booking(?:/(?P<id>\d+))?', [
			['methods' => WP_REST_Server::READABLE, 'callback' => [$this, 'read_booking'], 'permission_callback' => function ( \WP_REST_Request $request ) {
                return true;
            },],
			['methods' => WP_REST_Server::CREATABLE, 'callback' => [$this, 'create_booking'], 'permission_callback' => function ( \WP_REST_Request $request ) {
                return true;
            },],
			['methods' => WP_REST_Server::DELETABLE, 'callback' => [$this, 'delete_booking'], 'permission_callback' => function ( \WP_REST_Request $request ) {
                return true;
            },],
			['methods' => WP_REST_Server::EDITABLE, 'callback' => [$this, 'update_booking'], 'permission_callback' => function ( \WP_REST_Request $request ) {
                return true;
            }, 'login_user_id' => get_current_user_id()],
		], true);

		register_rest_route( 'events/v2', '/bookings', [
			['methods' => WP_REST_Server::READABLE, 'callback' => [$this, 'list_bookings'], 'permission_callback' => function ( \WP_REST_Request $request ) {
				return true;
			},],
		], true);
	}

	public function create_booking(\WP_REST_Request $request) : WP_REST_Response 
	{

		$errors = Booking::validate_request($request);
		if (!empty($errors)) {
			return new WP_REST_Response(['error' => 'invalid_booking', 'messages' => $errors], 400);
		}

		$booking = Booking::from_rest_request($request);
		
		if(!$booking->validate($request)) {
			return new WP_REST_Response(['errors' => $booking->errors], 412);
		}

		if(!$booking->save()) {
			return new WP_REST_Response(['errors' => $booking->errors], 507);
		}

		if(!$booking->id) {
			return new WP_REST_Response(['error' => __('Failed to create booking', 'events')], 500);
		}

		do_action('em_booking_add', $booking);

		$result = apply_filters('em_booking_response', [], $booking);

		return new WP_REST_Response(array_merge($result, [
			'errors' => $booking->errors,
			'booking_id' => $booking->id ?? null,
		]), 200);
	}

	public function update_booking(\WP_REST_Request $request) : WP_REST_Response 
	{
		$id = $request->get_param('id');
		$method = $request->get_method();

		if(!$id) {
			return new WP_REST_Response([
				'error' => __('No booking ID given', 'events')
			], 400);
		}

		$booking = Booking::get_by_id($id);

		if (!$booking) {
			return new WP_REST_Response(['error' => __('Booking no. ' . $id . ' not found', 'events')], 404);
		}

		if(!current_user_can('edit_published_posts')) {
			$response = [
				'error' => __('You do not have permission to edit this booking', 'events')
			];
			return new WP_REST_Response($response, 403);
		}

		if ($method === 'PUT') {
			
			$booking->apply_rest_data($request);
			if (!$booking->validate()) {
				return new WP_REST_Response([
					'errors' => $booking->errors,
				], 400);
			}
			$booking->save();
			return new WP_REST_Response([], 200);
		}

		if ($method === 'PATCH') {
			if (isset($request['action'])) {
				error_log('BookingController::update_booking - action: ' . $request['action']);
				[$code, $result] = $this->set_action($request['action'], $booking);
				return new WP_REST_Response($result, $code);
			}
			$booking->apply_partial_rest_data($request);
			return new WP_REST_Response(['errors' => $booking->errors], 200);
		}

		return new WP_REST_Response(
			['error' => __('Invalid request method', 'events')],
			405
		);
	}

	public function read_booking(\WP_REST_Request $request) : \WP_REST_Response 
	{
		$booking = $request->has_param('id') ? Booking::get_by_id($request->get_param('id')) : false;
		$event_id = $booking ? $booking->event_id : intval($request->get_param('event_id'));
		
		$event = $booking ? Event::get_by_id($event_id) : Event::find_by_post_id($event_id);
		if(!$event && !$booking) {
			return new WP_REST_Response(['error' => __('Booking not found', 'events')], 404);
		}

		$coupons = CouponCollection::from_event($event);

		$priceFormatter = new \Contexis\Events\Intl\Price(0);

		$data = [
			'rest_url' => get_rest_url(),
			'event' => $event->jsonSerialize(),
			'registration_fields' => BookingForm::get_booking_form($event->post_id),
		    'attendee_fields' => AttendeesForm::get_attendee_form($event->post_id),
			'available_tickets' => TicketCollection::find_by_event_id($event_id)->jsonSerialize(),
			'available_gateways' => GatewayCollection::active()->jsonSerialize(),
			'l10n' => [
				"consent" => get_option("dbem_privacy_message"),
				"donation" => get_option("dbem_donation_message"),
				"currency" => $priceFormatter->get_currency_code(),
				"locale" => str_replace('_', '-', get_locale()),
			],
			'available_coupons' => $coupons->jsonSerialize(),
			'booking' => $booking ? $booking->jsonSerialize() : null
		];

		return new WP_REST_Response($data, 200);
	}

	public function delete_booking(\WP_REST_Request $request) : \WP_REST_Response 
	{
		$id = $request->get_param('id');
		$booking = Booking::get_by_id(absint($id));
		$success = $booking->delete();

		return new WP_REST_Response(['success' => $success, 'errors' => $booking->errors], $success ? 200 : 400);
	}

	private function set_action(string $action, Booking $booking): array {
		if (!array_key_exists($action, $this->action_to_status)) {
			return [400, ['error' => __('Invalid action', 'events')]];
		}

		$status = $this->action_to_status[$action];

		if (!$booking->set_status($status)) {
			return [400, ['error' => __('Failed to set status', 'events'), 'details' => $booking->errors]];
		}

		return [200, [
			'success' => true,
			'status' => $booking->status->value,
			'status_text' => $booking->get_status()
		]];
	}

	public function list_bookings(\WP_REST_Request $request) : WP_REST_Response 
	{
		$args = [];
		if ($request->has_param('event_id')) {
			$args['event_id'] = absint($request->get_param('event_id'));
		}
		if ($request->has_param('status')) {
			$args['status'] = array_map('absint', explode(',', $request->get_param('status')));
		}
		if ($request->has_param('booking_mail')) {
			$args['booking_mail'] = sanitize_email($request->get_param('booking_mail'));
		}
		if ($request->has_param('booking_name')) {
			$args['booking_name'] = sanitize_text_field($request->get_param('booking_name'));
		}
		if ($request->has_param('limit')) {
			$args['limit'] = absint($request->get_param('limit'));
		} else {
			$args['limit'] = 100;
		}
		if ($request->has_param('offset')) {
			$args['offset'] = absint($request->get_param('offset'));
		}

		$bookings = BookingCollection::find($args);

		return new WP_REST_Response($bookings, 200);	
	}
}

