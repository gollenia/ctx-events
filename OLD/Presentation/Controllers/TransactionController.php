<?php

namespace Contexis\Events\Controllers;

use Contexis\Events\Models\Transaction;
use Contexis\Events\Collections\TransactionCollection;
use Contexis\Events\Repositories\TransactionRepository;
use WP_REST_Response;
use WP_REST_Server;
use WP_REST_Request;

class TransactionController {

	public static function init() {
		$instance = new self();
		add_action('rest_api_init', array($instance, 'register_rest_route') );
	}

	public function register_rest_route() : void {

		register_rest_route( 'events/v2', '/transactions', [
			['methods' => WP_REST_Server::READABLE, 'callback' => [$this, 'list_transactions'], 'permission_callback' => fn(\WP_REST_Request $request) => true],
		], true);
	}

	
	public function list_transactions(\WP_REST_Request $request) : WP_REST_Response 
	{
		$args = [];
		if ($request->has_param('booking_id')) {
			$args['booking_id'] = absint($request->get_param('booking_id'));
		}
		if ($request->has_param('status')) {
			$args['status'] = array_map('sanitize_text_field', explode(',', $request->get_param('status')));
		}
		if ($request->has_param('limit')) {
			$args['limit'] = absint($request->get_param('limit'));
		} else {
			$args['limit'] = 100;
		}
		if ($request->has_param('offset')) {
			$args['offset'] = absint($request->get_param('offset'));
		}
		
		$transactions = TransactionCollection::find($args);

		return new WP_REST_Response($transactions, 200);	
	}
}

