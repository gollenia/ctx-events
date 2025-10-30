<?php

namespace Contexis\Events\Payment;

use Contexis\Events\Models\Booking;
use Contexis\Events\Views\EventView;
use UserFields;

class GatewayService {

	private array $gateways = [];
	
	public function __construct()
	{
		add_action('wp_ajax_em_payment', array($this, 'handle_payment_gateways'), 10 );
		add_action('wp_ajax_nopriv_em_payment', array($this, 'handle_payment_gateways'), 10 );
		
		$this->gateways = $this->find_gateways();

		if( is_admin() ){
			GatewaysAdmin::init();
		}
	}

	private function find_gateways() : array {
		$gateways = [];
		foreach (glob(__DIR__.'/Gateways/*.php') as $file) {
			$base = basename($file, '.php');
			$class = __NAMESPACE__ . '\\Gateways\\' . $base;
			if (class_exists($class)) {
				$instance = new $class();
				if ($instance instanceof Gateway) {
					$gateways[$instance->slug] = $instance;
				}
			}
		}
		return $gateways;
	}

	public function get_gateways() : array {
		return $this->gateways;
	}

	public function find_gateway_by_slug($slug) : ?Gateway {
		if (array_key_exists($slug, $this->gateways)) {
			return $this->gateways[$slug];
		}
		return null;
	}


	public static function handle_payment_gateways() {
		if( !empty($_REQUEST['em_payment_gateway']) ) {
			do_action( 'em_handle_payment_return_' . $_REQUEST['em_payment_gateway']);
			exit();
		}
	}
	
	

}



do_action('em_gateways_init');

