<?php

namespace Contexis\Events\Payment;

use Contexis\Events\Collections\BookingCollection;
use Contexis\Events\Models\Booking;
use Contexis\Events\Models\Ticket;
use Contexis\Events\Options;
use Contexis\Events\PostTypes\EventPost;


class Gateway implements \Contexis\Events\Interfaces\Gateway {
	
	public protected(set) string $slug = '';
	public protected(set) string $title = '';

	public int $status = Booking::PENDING;
	
	public string $status_txt = '';
	
	public bool $button_enabled = false;
	public bool $payment_return = false;
	public bool $count_pending_spaces = false;


	function __construct() {
		// Actions and Filters, only if gateway is active
		
		if( $this->is_active() ){
			add_filter('em_booking_response', [$this, 'booking_form_feedback'], 10, 2);
			
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
	
	public function register_handle_payment_api(){
		register_rest_route( 'events/v1', '/gateways/'.$this->slug.'/notify', array(
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
			$booking->booking_status = $this->status; //status 4 = awaiting online payment
		}
	}

	function booking_form_feedback( array $return, Booking $booking ) : array{
		return $return;
	}

	

	function get_payment_info($booking){
		return array();
	}
	
	/**
	 * Run by EM_Gateways_Admin::handle_gateways_panel_updates() if this gateway has been updated. You should capture the values of your new fields above and save them as options here.
	 * @param $options array of option names that get updated when this gateway settings page is saved
	 * return boolean 
	 * @todo add $options as a parameter to method, and update all extending classes to prevent strict errors
	 */
	function update() {
		//custom options as well as ML options
		$function_args = func_get_args();
		$options = !empty($function_args[0]) ? $function_args[0]:array();
		//default action is to return true
		if($this->button_enabled){ 
			$options_wpkses[] = 'em_'.$this->slug . '_button';
			add_filter('update_em_'.$this->slug . '_button','wp_kses_post');
		}
		$options_wpkses[] = 'em_'.$this->slug . '_option_name';		
		$options_wpkses[] = 'em_'.$this->slug . '_option_description';
		$options_wpkses[] = 'em_'.$this->slug . '_form';
		//add filters for all $option_wpkses values so they go through wp_kses_post
		foreach( $options_wpkses as $option_wpkses ) add_filter('gateway_update_'.$option_wpkses,'wp_kses_post');
		$options = array_merge($options, $options_wpkses);	
		
		//go through the options, grab them from $_REQUEST, run them through a filter for sanitization and save 
		foreach( $options as $option_index => $option_name ){
			if( is_array( $option_name ) ){
				$option_values = array();
				foreach( $option_name as $option_key ){
				    $option_value_raw = !empty($_REQUEST[$option_index.'_'.$option_key]) ? stripslashes($_REQUEST[$option_index.'_'.$option_key]) : '';
				    $option_values[$option_key] = apply_filters('gateway_update_'.$option_index.'_'.$option_key, $option_value_raw);
				}
			    update_option($option_index, $option_values);
			}else{
			    $option_value_raw = !empty($_REQUEST[$option_name]) ? stripslashes($_REQUEST[$option_name]) : '';
			    $option_value = apply_filters('gateway_update_'.$option_name, $option_value_raw);
			    update_option($option_name, $option_value);
			}
		}
		do_action('em_updated_gateway_options', $options, $this);
		do_action('em_gateway_update', $this);
		return true;
	}

	public function handle_payment_return_api( $request ) : \WP_REST_Response {
		$message = 'Missing POST variables. Identification is not possible. If you are not '.$this->title.' and are visiting this page directly in your browser, this error does not indicate a problem, but simply means Events Manager is correctly set up and ready to receive communication from '.$this->title.' only.';
		return new \WP_REST_Response( array('message' => $message), 200 );
	}
	

	function handle_payment_return() {}
	
	function em_booking_get_status(string $message, Booking $booking) : string {
		if( !empty($this->status_txt) && $booking->booking_status == $this->status && $this->uses_gateway($booking) ){ 
			return $this->status_txt; 
		}
		return $message;
	}

	function get_rest_fields() {
		return array(
			'name' => $this->slug,
			"title" => get_option('em_'.$this->slug.'_option_name'),
        	"html" => get_option('em_'.$this->slug.'_form'),
			"description" => get_option('em_'.$this->slug.'_option_description'),
			"status_available" => $this->status
		);
	}
	
	function em_bookings_get_pending_spaces(int $count, BookingCollection $booking_collection) : int {
		global $wpdb;	
		$sql = 'SELECT SUM(booking_spaces) FROM '.EM_BOOKINGS_TABLE. ' WHERE booking_status=%d AND event_id=%d AND booking_meta LIKE %s';
		$gateway_filter = '%s:7:"gateway";s:'.strlen($this->slug).':"'.$this->slug.'";%';
		$pending_spaces = $wpdb->get_var( $wpdb->prepare($sql, array($this->status, $booking_collection->event_id, $gateway_filter)) );
		return max(0, (int)$pending_spaces) + $count;
	}
	
	function em_booking_is_reserved( bool $result, Booking $booking ) : bool {
		if($booking->booking_status == $this->status && $this->uses_gateway($booking) && get_option('dbem_bookings_approval_reserved')){
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
	
		$gateway_filter = '%s:7:"gateway";s:' . strlen($this->slug) . ':"' . $this->slug . '";%';
	
		$sql = $wpdb->prepare(
			"SELECT booking_meta 
			 FROM " . EM_BOOKINGS_TABLE . "
			 WHERE event_id = %d 
			 AND booking_status = %d 
			 AND booking_meta LIKE %s",
			$ticket->event_id, $this->status, $gateway_filter
		);
	
		$results = $wpdb->get_col($sql);
	
		$pending_spaces = 0;
	
		foreach ($results as $metadata) {
			$meta = json_decode($metadata, true);
			if (!empty($meta['attendees'][$ticket->ticket_id])) {
				$pending_spaces += count($meta['attendees'][$ticket->ticket_id]);
			}
		}
	
		return $pending_spaces + $count;
	}
	

	function handle_return_url(){
		if( !empty($_GET['payment_complete']) && $_GET['payment_complete'] == $this->slug ){
			//add actions for each page where a thank you might appear by default
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

	function get_option( $name ){
		return get_option('em_'.$this->slug.'_'.$name);
	}
	
	function update_option( $name, $value ){
		return update_option('em_'.$this->slug.'_'.$name, $value);
	}
	
	function uses_gateway(Booking $booking){
		return (!empty($booking->booking_meta['gateway']) && $booking->booking_meta['gateway'] == $this->slug);
	}


	function get_payment_return_url(){
		return admin_url('admin-ajax.php?action=em_payment&em_payment_gateway='.$this->slug);
	}
	
	function get_payment_return_api_url(){
		return get_rest_url( null, 'events/v1/gateways/'.$this->slug.'/notify' );
	}

	function record_transaction($booking, $amount, $currency, $timestamp, $txn_id, $payment_status, $note) {
		$data = array();
		$data['booking_id'] = $booking->booking_id;
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

	function toggle_activation() {
		$active = get_option('em_payment_gateways');

		if(isset($this->slug, $active)) {
			unset($active[$this->slug]);
			update_option('em_payment_gateways',$active);
		}

		$active[$this->slug] = true;
		update_option('em_payment_gateways',$active);
	}

	function is_active() {
		$active = get_option('em_payment_gateways', array());
		$is_active = array_key_exists($this->slug, $active);
		
		return $is_active;			
		
	}

	function gateway_settings() : void {}

	function settings() {

		if(!method_exists($this, 'gateway_settings')) return;
		$messages['updated'] = esc_html__('Gateway updated.', 'events');
		$messages['error'] = esc_html__('Gateway not updated.', 'events');
		?>
	    
		<div class='wrap nosubsub'>
			<h1><?php echo sprintf(__('Edit %s settings','events'), esc_html($this->title) ); ?></h1>
			<?php
			if ( isset($_GET['msg']) && !empty($messages[$_GET['msg']]) ){ 
				echo '<div id="message" class="'.$_GET['msg'].' fade"><p>' . $messages[$_GET['msg']] . 
				' <a href="'.add_query_arg(['action'=>null,'gateway'=>null, 'msg' => null], $_SERVER['REQUEST_URI']).'">'.esc_html__('Back to gateways','events').'</a>'.
				'</p></div>';
			}
			?>
			<form action='' method='post' name='gatewaysettingsform' class="em-gateway-settings">
				<input type='hidden' name='action' id='action' value='updated' />
				<input type='hidden' name='gateway' id='gateway' value='<?php echo $this->slug; ?>' />
				<?php wp_nonce_field('updated-' . $this->slug); ?>
				<h3><?php echo sprintf(esc_html( '%s Options', 'events'),esc_html('Booking Form','events')); ?></h3>
				<table class="form-table">
				<tbody>
                    <?php
                        $desc = __('The user will see this as the text option when choosing a payment method.','events'); 
                        Options::input(__('Gateway Title', 'events'), 'em_'.$this->slug.'_option_name', $desc);

						$desc = __('This message will be shown to the user when they select this gateway.','events');
						Options::textarea(__('Gateway Description', 'events'), 'em_'.$this->slug.'_option_description', $desc);

                        $desc = __('If a user chooses to pay with this gateway, or it is selected by default, this message will be shown just below the selection.', 'events'); 
                        Options::textarea(__('Booking Form Information', 'events'), 'em_'.$this->slug.'_form', $desc); 
                    ?>
				</tbody>
				</table>
				<?php
					$this->gateway_settings();
				 	do_action('em_gateway_settings_footer', $this); 
				?>
				<p class="submit">
					<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
				</p>
			</form>
		</div> 
		<?php
	}

}