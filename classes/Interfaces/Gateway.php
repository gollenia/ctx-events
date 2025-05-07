<?php

namespace Contexis\Events\Interfaces;

interface Gateway {
	public string $slug { get; }
	public string $title { get; }

	public int $status { get; set;}
	
	public string $status_txt { get; set;}
	
	public bool $button_enabled { get; set;}
	public bool $payment_return { get; set;}
	public bool $count_pending_spaces { get; set;}

	public function handle_payment_return_api( $request ) : \WP_REST_Response;

}

interface GatewayWithSettings extends Gateway {
	function gateway_settings();
}