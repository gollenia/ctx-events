<?php

use Contexis\Events\Collections\BookingCollection;
use Contexis\Events\Model\Booking;
use Contexis\Events\Options;
use Contexis\Events\Models\Event;

/**
 * This Gateway is slightly special, because as well as providing functions that need to be activated, there are offline payment functions that are always there e.g. adding manual payments.
 * @author marcus
 */
class EM_Gateway_Offline extends EM_Gateway {

	var $gateway = 'offline';
	var $title = 'Offline';
	var $status = 5;
	var $button_enabled = true;
	var $count_pending_spaces = true;
	var $supports_multiple_bookings = true;

	/**
	 * Sets up gateway and registers actions/filters
	 */
	function __construct() {
		parent::__construct();
		add_action('init',array(&$this, 'actions'),10);
		add_action('rest_api_init', array($this, 'register_rest_route'));
		add_filter('em_booking_set_status',array(&$this,'em_booking_set_status'),1,2);
		add_filter('em_bookings_pending_count', array(&$this, 'em_bookings_pending_count'),1,1);
		add_filter('em_wp_localize_script', array(&$this,'em_wp_localize_script'),1,1);
		add_filter('em_booking_validate', array(&$this,'em_booking_validate'),9,2); //before Bookings_Form hooks in
	}
	
	/**
	 * Run on init, actions that need taking regarding offline bookings are caught here, e.g. registering manual bookings and adding payments 
	 */
	
	
	function em_wp_localize_script($vars){
		if( is_user_logged_in() && get_option('dbem_rsvp_enabled') ){
			$vars['offline_confirm'] = __('Be aware that by approving a booking awaiting payment, a full payment transaction will be registered against this booking, meaning that it will be considered as paid.','events');
		}
		return $vars;
	}

	function actions(){
		if( is_user_logged_in() && get_option('dbem_rsvp_enabled') ){
			add_action('em_booking_add_'.$this->gateway, array(&$this, 'booking_add'), 10, 2);
			add_action('em_booking_form_footer', array(&$this, 'em_booking_form_footer'), 10, 1);
		}
	}
	
	/**
	 * Intercepts return JSON and adjust feedback messages when booking with this gateway.
	 * @param array $return
	 * @param Booking $booking
	 * @return array
	 */
	function booking_form_feedback( $result, Booking $booking ){
		if(!get_option("em_offline_iban", true)) return [
			'success' => false,
			'error' => "No IBAN available. Please add an IBAN in the offline payment gateway"
		];
		
		$event = Event::find_by_id($booking->event_id);

		$result['gateway'] = [
			"purpose" => $booking->booking_id . "-" . $event->post_name . "-" . $booking->booking_meta['registration']['last_name'],
			"iban" => get_option("em_offline_iban", true),
			"beneficiary" => get_option("em_offline_beneficiary", true),
			"bic" => get_option("em_offline_bic", true),
			"bank" => get_option("em_offline_bank", true),
			"amount" => $booking->booking_price,
			"deadline" => get_option("em_offline_deadline", true),
			"title" => $this->title,
			"message" => get_option("em_offline_booking_feedback", true)
		];			
		return $result;
	}

	
	
	/**
	 * Sets booking status and records a full payment transaction if new status is from pending payment to completed. 
	 * @param int $status
	 * @param Booking $booking
	 */
	function em_booking_set_status($result, $booking){
		if($booking->booking_status == 1 && $booking->previous_status == $this->status && $this->uses_gateway($booking) && (empty($_REQUEST['action']) || $_REQUEST['action'] != 'gateway_add_payment') ){
			$this->record_transaction($booking, $booking->get_price(false,false,true), get_option('dbem_bookings_currency'), current_time('mysql'), '', 'Completed', '');								
		}
		return $result;
	}
	
	function em_bookings_pending_count($count){
		$booking_collection = BookingCollection::get(array('status'=>5));
		return $count + $booking_collection->count();
	}
	


	/**
	 * Modifies the booking status if the event isn't free and also adds a filter to modify user feedback returned.
	 * Triggered by the em_booking_add_yourgateway action.
	 * @param Event $event
	 * @param Booking $booking
	 * @param boolean $post_validation
	 */
	function booking_add($booking, $post_validation = false){
		//validate post
		if( !empty($_REQUEST['payment_amount']) && !is_numeric($_REQUEST['payment_amount'])){
			$booking->add_error( 'Invalid payment amount, please provide a number only.', 'events' );
		}
		//add em_event_save filter to log transactions etc.
		add_filter('em_booking_save', array(&$this, 'em_booking_save'), 10, 2);
		//set flag that we're manually booking here, and set gateway to offline
	
		
		parent::booking_add($booking, $post_validation);
	}
	
	/**
	 * Hooks into the em_booking_save filter and checks whether a partial or full payment has been submitted
	 * @param boolean $result
	 * @param Booking $booking
	 */
	function em_booking_save( $result, $booking ){
		if( $result && !empty($_REQUEST['manual_booking']) && wp_verify_nonce($_REQUEST['manual_booking'], 'em_manual_booking_'.$_REQUEST['event_id']) ){
			remove_filter('em_booking_set_status',array(&$this,'em_booking_set_status'),1,2);
			if( !empty($_REQUEST['payment_full']) ){
				$price = ( !empty($_REQUEST['payment_amount']) && is_numeric($_REQUEST['payment_amount']) ) ? $_REQUEST['payment_amount']:$booking->get_price(false, false, true);
				$this->record_transaction($booking, $price, get_option('dbem_bookings_currency'), current_time('mysql'), '', 'Completed', __('Manual booking.','events'));
				$booking->set_status(1,false);
			}elseif( !empty($_REQUEST['payment_amount']) && is_numeric($_REQUEST['payment_amount']) ){
				$this->record_transaction($booking, $_REQUEST['payment_amount'], get_option('dbem_bookings_currency'), current_time('mysql'), '', 'Completed', __('Manual booking.','events'));
				if( $_REQUEST['payment_amount'] >= $booking->get_price(false, false, true) ){
					$booking->set_status(1,false);
				}
			}
			add_filter('em_booking_set_status',array(&$this,'em_booking_set_status'),1,2);
			
		}
		return $result;
	}
	
	
	
	function em_booking_validate($result, $booking){
		if( !empty($_REQUEST['manual_booking']) && wp_verify_nonce($_REQUEST['manual_booking'], 'em_manual_booking_'.$_REQUEST['event_id']) ){
			
		}
		return $result;
	}
	
	
	

	/**
	 * Called instead of the filter in EM_Gateways if a manual booking is being made
	 * @param Event $event
	 */
	function em_booking_form_footer($event){
		if(!current_user_can('edit_published_posts')) return;
		
		?>
		<input type="hidden" name="gateway" value="<?php echo $this->gateway; ?>" />
		<input type="hidden" name="manual_booking" value="<?php echo wp_create_nonce('em_manual_booking_'.$event->event_id); ?>" />
		<p class="em-booking-gateway" id="em-booking-gateway">
			<label><?php _e('Amount Paid','events'); ?></label>
			<input type="text" name="payment_amount" id="em-payment-amount" value="<?php if(!empty($_REQUEST['payment_amount'])) echo esc_attr($_REQUEST['payment_amount']); ?>">
			<?php _e('Fully Paid','events'); ?> <input type="checkbox" name="payment_full" id="em-payment-full" value="1"><br />
			<em><?php _e('If you check this as fully paid, and leave the amount paid blank, it will be assumed the full payment has been made.' ,'events'); ?></em>
		</p>
		<?php
		
		return;
	}
	
	/* 
	 * --------------------------------------------------
	 * Settings pages and functions
	 * --------------------------------------------------
	 */
	
	/**
	 * Outputs custom offline setting fields in the settings page 
	 */
	function mysettings() {

		?>
		<table class="form-table">
		<tbody>
		  <?php 
		  	  Options::input( esc_html__('Success Message', 'events'), 'em_'. $this->gateway . '_booking_feedback', esc_html__('The message that is shown to a user when a booking with offline payments is successful.','events') );
			  Options::input( esc_html__('IBAN', 'events'), 'em_'. $this->gateway . '_iban', esc_html__('In order to generate a QR Code for payment, you have to provide a valid IBAN','events'), ["class" => 'regular-text code', 'pattern' => '[A-Z0-9]'] );
			  Options::input( esc_html__('BIC', 'events'), 'em_'. $this->gateway . '_bic', esc_html__('Though not needed, some banks are only happy if you provide a BIC','events'), ["class" => 'regular-text code', 'pattern' => '[A-Z0-9]'] );			  
			  Options::input( esc_html__('Bank', 'events'), 'em_'. $this->gateway . '_bank', esc_html__('Same goes with Bank name.','events') );
			  Options::input( esc_html__('Beneficiary', 'events'), 'em_'. $this->gateway . '_beneficiary', esc_html__('In some countries you need to specify a beneficiary. This Data is added to the QR Code.','events') );
			  Options::input( esc_html__('Payment Deadline', 'events'), 'em_'. $this->gateway . '_deadline', esc_html__('Number of days until payment has to be made','events'), ["placeholder" => "10", "type" => Options::NUMBER, "class" => 'regular-text code', 'pattern' => '[0-9]'] );
		  ?>
		</tbody>
		</table>
		<?php
	}

	/* 
	 * Run when saving  settings, saves the settings available in EM_Gateway_Mollie::mysettings()
	 */
	function update() {
	    $gateway_options = [
			'em_'. $this->gateway . '_booking_feedback',
			'em_'. $this->gateway . '_iban',
			'em_'. $this->gateway . '_bic',
			'em_'. $this->gateway . '_bank',
			'em_'. $this->gateway . '_beneficiary',
			'em_'. $this->gateway . '_deadline',
		];
		foreach( $gateway_options as $option_wpkses ) add_filter('gateway_update_'.$option_wpkses,'wp_kses_post');
		return parent::update($gateway_options);
	}	
	
	/**
	 * Checks an booking object and returns whether or not this gateway is/was used in the booking.
	 * @param Booking $booking
	 * @return boolean
	 */
	function uses_gateway($booking){
	    //for all intents and purposes, if there's no gateway assigned but this booking status matches, we assume it's offline
		return parent::uses_gateway($booking) || ( empty($booking->booking_meta['gateway']) && $booking->booking_status == $this->status );
	}


	function register_rest_route() {
		register_rest_route( 'events/v2', '/gateway/offline', [
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => [$this, 'add_payment'],
			'permission_callback' => function () {
    			return current_user_can('manage_bookings');
			}
		]);
	}


	function add_payment($request) {
		$booking = Booking::get_by_id($request['booking_id']);

		if( !empty($request['transaction_total_amount']) && is_numeric($request['transaction_total_amount']) ){
			$this->record_transaction($booking, $_REQUEST['transaction_total_amount'], get_option('dbem_bookings_currency'), current_time('mysql'), '', 'Completed', $_REQUEST['transaction_note']);
			$total = $booking->get_total_paid();
			if( $total >= $booking->get_price() ){
				$booking->approve();
			}
			do_action('em_payment_processed', $booking, $this);
		}
	}

}
EM_Gateways::register_gateway('offline', 'EM_Gateway_Offline');
require_once('QRCode.php');

?>