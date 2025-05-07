<?php

namepace Contexis\Events\Controllers;

class GatewayController
{
	public static function init() : self {
		$instance = new self();
		add_action('rest_api_init', [$instance, 'register_rest_routes']);
		return new self();
	}

	public function register_rest_routes() {
		register_rest_route('events/v2', '/gateways', [
			'methods' => \WP_REST_Server::READABLE,
			'callback' => [$this, 'handle_gateway_request'],
			'permission_callback' => '__return_true',
		]);
	}

	public function handle_gateway_request(\WP_REST_Request $request) {
		$gateways = \Contexis\Events\Gateways\Gateway::get_gateways();
		$gateways = array_map(function($gateway) {
			return $gateway->get_data();
		}, $gateways);

		return new \WP_REST_Response($gateways, 200);
	}
}