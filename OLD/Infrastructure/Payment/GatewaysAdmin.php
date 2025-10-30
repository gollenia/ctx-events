<?php

namespace Contexis\Events\Payment;

use Contexis\Events\PostTypes\EventPost;


class GatewaysAdmin {

	use \Contexis\Events\Core\Contracts\Application;
	
	public static function init() : self {
		$instance = new self();
		add_action('admin_menu', [$instance, 'admin_menu'],10,1);
		
		add_filter('em_bookings_table_rows_col', array($instance,'em_bookings_table_rows_col'),10,5);
		add_filter('em_bookings_table_cols_template', array($instance,'em_bookings_table_cols_template'),10,2);
		return $instance;
	}
	
	public function admin_menu($plugin_pages){
		add_submenu_page(
			'edit.php?post_type=' . EventPost::POST_TYPE, 
			__('Payment Gateways','events'),
			__('Payment Gateways','events'),
			'list_users','events-gateways',
			 [$this, 'handle_gateways_panel'],
			 20
		);
	}




	public function handle_gateways_panel() {
		 echo '
    <div class="wrap">
      
        <div id="gateway-admin"></div>
      
    </div>';

	
		
	}

	public function em_bookings_table_rows_col($column, $booking, $format) : string
	{
		if ($column != 'gateway') return '';
		if( empty($booking->metadata['gateway']) ) return __('None','events');
		$gateway = $this->app()->get(GatewayService::class)->find_gateway_by_slug($booking->metadata['gateway']);
		return $gateway->title;
	}
	
	public function em_bookings_table_cols_template($template, $bookings_table){
		$template['gateway'] = __('Gateway Used','events');
		return $template;
	}
			
	
}
