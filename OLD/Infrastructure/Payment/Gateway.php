<?php

namespace Contexis\Events\Payment;

use Contexis\Events\Collections\BookingCollection;
use Contexis\Events\Models\Booking;
use Contexis\Events\Models\BookingStatus;
use Contexis\Events\Models\Ticket;
use WP_REST_Request;

class Gateway implements \Contexis\Events\Core\Contracts\Gateway, \JsonSerializable {
	
	public string $slug = '';
	public string $title = '';
	public string $description = '';
	public string $feedback = '';

	private array $options = [];
	public array $allowed_options = [
		'title', 'description', 'feedback'
	];

	public BookingStatus $status = BookingStatus::PENDING;

	public string $status_txt = '';
	
	public bool $payment_return = false;
	public bool $count_pending_spaces = false;


	function __construct() {
		
		if( $this->is_active() ){
			add_filter('em_booking_response', [$this, 'booking_payment_feedback'], 10, 2);
			
			if( $this->payment_return ){
				add_action('em_handle_payment_return_' . $this->slug, array(&$this, 'handle_payment_return')); 
				add_action('rest_api_init', array( $this, 'register_handle_payment_api' ));
			}
			if(!empty($this->status_txt)){
				//Booking UI
				add_filter('em_my_bookings_booked_message',array(&$this,'em_my_bookings_booked_message'),10,2);
				add_filter('em_booking_get_status',array(&$this,'em_booking_get_status'),10,2);
			}
		}
		if( $this->count_pending_spaces ){
			//Modify spaces calculations, required even if inactive, due to previously made bookings whilst this may have been active
			add_filter('em_bookings_get_pending_spaces', array(&$this, 'em_bookings_get_pending_spaces'),1,3);
			
			add_filter('em_booking_is_reserved', array(&$this, 'em_booking_is_reserved'),1,2);
			add_filter('em_booking_is_pending', array(&$this, 'em_booking_is_pending'),1,2);
		}
		//checkout-specific functions for redirects
		$this->handle_return_url();
		
	}

	public function set_option( string $key, $value ) : bool {
		if (!in_array($key, $this->allowed_options, true)) {
			return false;
		}

		if (property_exists($this, $key)) {
			$this->$key = $value;
		}

		$this->options[$key] = $value;
		return true;
	}

	public function get_option(string $key, mixed $default = null): mixed {
		return $this->options[$key] ?? $this->$key ?? $default;
	}
	
	public function register_handle_payment_api(){
		register_rest_route( 'events/v2', '/gateways/'.$this->slug.'/notify', array(
			array(
				'methods'  => 'GET,POST',
				'callback' => array( $this, 'handle_payment_return_api' ),
				'permission_callback' => array($this, 'gateway_api_permission')
			)
		) );
	}

	public function gateway_api_permission() {
		return true;
	}

	function booking_add($booking, $post_validation = false){
		if( $booking->get_price() > 0 ){
			$booking->booking_status = $this->status; 
		}
	}

	function booking_payment_feedback( array $return, Booking $booking ) : array{
		return $return;
	}

	function get_payment_info(Booking $booking) : array {
		return [];
	}

	function update() {
		$result = [];
		foreach($this->options as $option => $value){
			if(!update_option('em_'.$this->slug.'_'.$option, $value)){
				$result[] = __('Error saving option', 'events') . ': ' . $option . ' = ' . $value;
			}
		}
		do_action('em_gateway_update', $this);
		return $result;
	}

	public function handle_payment_return_api( WP_REST_Request$request ) : \WP_REST_Response {
		$message = 'Missing POST variables. Identification is not possible. If you are not '.$this->title.' and are visiting this page directly in your browser, this error does not indicate a problem, but simply means Events Manager is correctly set up and ready to receive communication from '.$this->title.' only.';
		return new \WP_REST_Response( array('message' => $message), 200 );
	}
	

	function handle_payment_return() {}
	
	function em_booking_get_status(string $message, Booking $booking) : string {
		if( !empty($this->status_txt) && $booking->status->value == $this->status && $this->uses_gateway($booking) ){ 
			return $this->status_txt; 
		}
		return $message;
	}
	
	function em_bookings_get_pending_spaces(int $count, BookingCollection $booking_collection) : int {
		global $wpdb;	
		$sql = 'SELECT SUM(spaces) FROM '.EM_BOOKINGS_TABLE. ' WHERE status=%d AND event_id=%d AND meta LIKE %s';
		$gateway_filter = '%s:7:"gateway";s:'.strlen($this->slug).':"'.$this->slug.'";%';
		$pending_spaces = $wpdb->get_var( $wpdb->prepare($sql, array($this->status, $booking_collection->event_id, $gateway_filter)) );
		return max(0, (int)$pending_spaces) + $count;
	}
	
	function em_booking_is_reserved( bool $result, Booking $booking ) : bool {
		if($booking->status->value == $this->status && $this->uses_gateway($booking) && get_option('dbem_bookings_approval_reserved')){
			return true;
		}
		return $result;
	}
	
	function em_booking_is_pending( $result, $booking ){
		if( $booking->booking_status == $this->status  && $this->uses_gateway($booking) && $this->count_pending_spaces ){
			return true;
		}
		return $result;
	}
	
	
	function em_ticket_get_pending_spaces(int $count, Ticket $ticket) : int {
		global $wpdb;
	
		$pending_spaces = 0;
	
		$bookings = BookingCollection::find([
			'event_id' => $ticket->event_id,
			'status' => $this->status,
			'gateway' => $this->slug
		]);
	
		foreach ($bookings as $booking) {
			if (!empty($booking->attendees[$ticket->ticket_id])) {
				$pending_spaces += count($booking->attendees[$ticket->ticket_id]);
			}
		}
	
		return $pending_spaces + $count;
	}
	

	function handle_return_url(){
		if( !empty($_GET['payment_complete']) && $_GET['payment_complete'] == $this->slug ){
			add_action('em_template_my_bookings_header', array(&$this, 'thank_you_message'));
			add_action('em_booking_form_top', array(&$this, 'thank_you_message'));
		}
	}
	
	function thank_you_message() : void {
		echo "<div class='em-booking-message em-booking-message-success'>".get_option('em_'.$this->slug.'_booking_feedback_completed').'</div>';
	}

	function get_return_url( ?Booking $booking = null ) : string {
		if( get_option('em_'. $this->slug . "_return" ) ){
			return get_option('em_'. $this->slug . "_return" );
		}
		
		$my_bookings_url = $booking ? get_post_permalink($booking->get_event()->event_id) : get_home_url();
		return add_query_arg('payment_complete', $this->slug, $my_bookings_url);
	}
	
	function get_cancel_url( $booking ){
		if( get_option('em_'. $this->slug . "_cancel" ) ){
			return get_option('em_'. $this->slug . "_cancel" );
		}else{
			$my_bookings_url = get_post_permalink($booking->get_event()->event_id);
			return add_query_arg('payment_cancelled', $this->slug, $my_bookings_url);
		}
	}

	
	function uses_gateway(Booking $booking){
		return ($booking->gateway == $this->slug);
	}


	function get_payment_return_url(){
		return admin_url('admin-ajax.php?action=em_payment&em_payment_gateway='.$this->slug);
	}
	
	function get_payment_return_api_url(){
		return get_rest_url( null, 'events/v1/gateways/'.$this->slug.'/notify' );
	}

	function jsonSerialize() : array {
		return array(
			'slug' => $this->slug,
			'title' => $this->title,
			'description' => $this->description,
			'active' => $this->is_active(),
			'info' => get_option('em_'.$this->slug.'_form')
		);
	}

	function record_transaction($booking, $amount, $currency, $timestamp, $txn_id, $payment_status, $note) {
		$data = array();
		$data['booking_id'] = $booking->id;
		$data['transaction_gateway_id'] = $txn_id;
		$data['transaction_timestamp'] = $timestamp;
		$data['transaction_currency'] = $currency;
		$data['transaction_status'] = $payment_status;
		$data['transaction_total_amount'] = $amount;
		$data['transaction_note'] = $note;
		$data['transaction_gateway'] = $this->slug;

		return;

		$booking->add_transaction($data);
	}

	public function toggle_activation(): bool {
		$active = get_option('em_payment_gateways', []);
	
		$is_active = isset($active[$this->slug]);
	
		if ($is_active) {
			unset($active[$this->slug]);
		} else {
			$active[$this->slug] = true;
		}
	
		update_option('em_payment_gateways', $active);
	
		return !$is_active;
	}

	function is_active() : bool {
		$active = get_option('em_payment_gateways', []);
		return array_key_exists($this->slug, $active);
	}

	function get_settings_fields() : array {
		return array(
			[
				'label' => __('Gateway Title', 'events'),
				'id' => 'title',
				'type' => 'text',
				'help' => __('The user will see this as the text option when choosing a payment method.','events'),
				'placeholder' => $this->title,
				'value' => get_option('em_'.$this->slug.'_title', $this->title),
			],
			[
				'label' => __('Gateway Description', 'events'),
				'id' => 'description',
				'type' => 'textarea',
				'help' => __('This message will be shown to the user when they select this gateway.','events'),
				'placeholder' => $this->description,
				'value' => get_option('em_'.$this->slug.'_description', $this->description),
			],
			[
				'label' => __('Booking Feedback', 'events'),
				'id' => 'feedback',
				'type' => 'textarea',
				'help' => __('If a user chooses to pay with this gateway, this message will be shown at the end of the ordering process.', 'events'),
				'placeholder' => $this->feedback,
				'value' => get_option('em_'.$this->slug.'_feedback', $this->feedback),
			],
		);
	}

}