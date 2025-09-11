<?php

namespace Contexis\Events\Core\Contracts;

use Contexis\Events\Models\BookingStatus;

interface Gateway {
	public string $slug { get; }
	public string $title { get; }

	public BookingStatus $status { get; set;}
	
	public string $status_txt { get; set;}
	
	public bool $payment_return { get; set;}
	public bool $count_pending_spaces { get; set;}

}

interface GatewayWithSettings extends Gateway {
	function gateway_settings();
}