<?php

namespace Contexis\Events\Controllers;

use Contexis\Events\Core\Container;
use Contexis\Events\Core\Request;
use Contexis\Events\Payment\GatewayCollection;
use \WP_REST_Server;

class GatewayController
{
	
	public static function init() : self {
		$instance = new self();
		add_action('rest_api_init', [$instance, 'register_rest_routes']);

		add_action('wp_ajax_toggle_gateway', [$instance, 'toggle_gateway']);
		return new self();
	}

	public function register_rest_routes() {
		
		register_rest_route( 'events/v2', '/gateways', [
			['methods' => WP_REST_Server::READABLE, 'callback' => [$this, 'read_gateways'], 'permission_callback' => function ( \WP_REST_Request $request ) {
                return true;
            },]
		], true);
		register_rest_route( 'events/v2', '/gateway', [
			['methods' => WP_REST_Server::READABLE, 'callback' => [$this, 'read_gateway'], 'permission_callback' => function ( \WP_REST_Request $request ) {
                return true;
            },],
			['methods' => WP_REST_Server::EDITABLE, 'callback' => [$this, 'update_gateway'], 'permission_callback' => function ( \WP_REST_Request $request ) {
				return true;
			},]
		], true);

		register_rest_route( 'events/v2', '/gateway/toggle', [
			['methods' => WP_REST_Server::EDITABLE, 'callback' => [$this, 'toggle_gateway'], 'permission_callback' => function ( \WP_REST_Request $request ) {
				return true;
			},]
		], true);
		
	}

	public function read_gateways(\WP_REST_Request $request) {
		$gateways = !is_user_logged_in() || $request->get_param('active') ? GatewayCollection::active() : GatewayCollection::all();
		return new \WP_REST_Response($gateways->jsonSerialize(), 200);
	}

	public function read_gateway(\WP_REST_Request $request) : \WP_REST_Response {
		$slug = $request->get_param('slug');
		$gateway = GatewayCollection::all()->get($slug);
		if (!$gateway) {
			return new \WP_REST_Response('Unknown gateway', 404);
		}
		if(!is_user_logged_in()) {
			return new \WP_REST_Response($gateway->jsonSerialize(), 200);
		}
		
		$response = $gateway->jsonSerialize();
		$response['settings'] = $gateway->get_settings_fields();
		return new \WP_REST_Response($response, 200);
	}

	public function update_gateway(\WP_REST_Request $request) : \WP_REST_Response {
		$slug = $request->get_param('slug');
		$gateway = GatewayCollection::all()->get($slug);
	
		if (!$gateway) {
			return new \WP_REST_Response('Unknown gateway', 404);
		}

		$result = [
			'active' => $gateway->is_active(),
			'settings' => [],
		];

		$settings = $request->get_param('settings');
			
		if (!is_array($settings)) return new \WP_REST_Response('Invalid settings', 404);

		foreach ($settings as $setting) {
			$gateway->set_option($setting['id'], $setting['value']);
			$result['settings'][$setting['id']] = $setting['value'];
		}
		
		$res = $gateway->update();
		$result['res'] = $res;
		return new \WP_REST_Response($result, 200);
		
	}

	function toggle_gateway (\WP_REST_Request $request) : \WP_REST_Response {
		$slug = $request->get_param('slug');
		$gateway = GatewayCollection::all()->get($slug);
	
		if (!$gateway) {
			return new \WP_REST_Response('Unknown gateway', 404);
		}
	
		$result = $gateway->toggle_activation();
	
		return new \WP_REST_Response($result, 200);
		
	}

	function save_gateway () {
		check_ajax_referer('events_gateways');
	
		$slug = Container::getInstance()->get(Request::class)->string('gateway');
		$gateway = GatewayCollection::all()->get($slug);
	
		if (!$gateway) {
			wp_send_json_error('Unknown gateway');
		}
	
		$result = $gateway->update();
	
		if ($result) {
			wp_send_json_success([
				'message' => __('Settings saved', 'events'),
			]);
		} else {
			wp_send_json_error(__('Failed to save settings', 'events'));
		}
	}
}