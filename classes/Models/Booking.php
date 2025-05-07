<?php

namespace Contexis\Events\Models;

use Contexis\Events\Collections\TicketCollection;
use Contexis\Events\Intl\Price;
use Contexis\Events\Collections\TicketsBookings;
use Contexis\Events\Models\Event;
use Contexis\Events\Payment\GatewayService;
use Contexis\Events\PostTypes\EventPost;
use Contexis\Events\Views\BookingView;
use Contexis\Events\Views\EventView;
use DateTime;
use WP_REST_Request;
use DateInterval;

/**
 * Contains all information and relevant functions surrounding a single booking made with Events Manager
 * @property int|false $booking_status
 * @property string $language
 */
class Booking {

	const PENDING = 0;
	const APPROVED = 1;
	const REJECTED = 2;
	const CANCELED = 3;
	const AWAITING_ONLINE_PAYMENT = 4;
	const AWAITING_PAYMENT = 5;
	const PAYMENT_FAILED = 6;
	const DELETED = 9;
	
	public int $booking_id = 0;
	public int $event_id = 0;
	public float $booking_price = 0.0;
	public float $booking_donation = 0.0;
	public int $booking_spaces = 0;
	public string $booking_comment = "";
	public int $booking_status = self::PENDING;
	public array $booking_meta = []; 
	public string $booking_mail;

	public array $fields = array(
		'booking_id' => array('name'=>'id','type'=>'%d'),
		'event_id' => array('name'=>'event_id','type'=>'%d'),
		'booking_mail' => array('name'=>'email','type'=>'%s'),
		'booking_price' => array('name'=>'price','type'=>'%f'),
		'booking_spaces' => array('name'=>'spaces','type'=>'%d'),
		'booking_comment' => array('name'=>'comment','type'=>'%s'),
		'booking_status' => array('name'=>'status','type'=>'%d'),
		'booking_donation' => array('name'=>'donation','type'=>'%f'),
		'booking_meta' => array('name'=>'meta','type'=>'%s'),
	);

	public array $notes;
	public ?DateTime $booking_date = null;
	public array $required_fields = [ 'booking_id', 'event_id', 'booking_spaces' ];
	public string $feedback_message = "";
	public array $errors = [];
	
	public int $mails_sent = 0;
	
	public int $previous_status = self::PENDING;
	public array $status_array = [];

	public bool $manage_override;

	public static function from_booking_id(int $booking_id) : Booking 
	{
		global $wpdb;
		$sql = $wpdb->prepare("SELECT * FROM ". EM_BOOKINGS_TABLE ." WHERE booking_id =%d", $booking_id);
		$booking = $wpdb->get_row($sql, ARRAY_A);
		return self::from_array($booking);
	}

	public static function from_array( array $array = array() ) : Booking 
	{	
		if(!is_array($array)) return new Booking();	
		$instance = new self();
		
		$instance->booking_meta = json_decode($array['booking_meta'], true);
		$instance->booking_date = new DateTime($array['booking_date']);
		$instance->booking_mail = $instance->booking_meta['registration']['user_email'] ?? "";
		foreach($array as $key => $value){
			if( in_array($key, ['booking_meta', 'booking_date']) ) continue;
			if( property_exists($instance, $key) ){
				$instance->$key = $value;
			}
		}
		
		$instance->previous_status = $instance->booking_status;
		return $instance;
	}

	public function get_fields( $inverted_array=false ){
		if( is_array($this->fields) ){
			$return = array();
			foreach($this->fields as $fieldName => $fieldArray){
				if($inverted_array){
					if( !empty($fieldArray['name']) ){
						$return[$fieldArray['name']] = $fieldName;
					}else{
						$return[$fieldName] = $fieldName;
					}
				}else{
					$return[$fieldName] = $fieldArray['name'];
				}
			}
			return apply_filters('em_object_get_fields', $return, $this, $inverted_array);
		}
		return apply_filters('em_object_get_fields', array(), $this, $inverted_array);
	}

	


	public static function get_by_id(int $id): Booking 
	{
		global $wpdb;
		$sql = $wpdb->prepare("SELECT * FROM ". EM_BOOKINGS_TABLE ." WHERE booking_id =%d", $id);
		
		$booking = $wpdb->get_row($sql, ARRAY_A);
		if ($booking) {
			return new Booking($booking);
		}
		return new Booking();
	}

	public static function get_status_array() : array 
	{
		return array(
			self::PENDING => __('Pending','events'),
			self::APPROVED => __('Approved','events'),
			self::REJECTED => __('Rejected','events'),
			self::CANCELLED => __('Cancelled','events'),
			self::AWAITING_ONLINE_PAYMENT => __('Awaiting Online Payment','events'),
			self::AWAITING_PAYMENT => __('Awaiting Payment','events'),
			self::DELETED => __('Deleted','events'),
		);
	}

	public static function get_status_label( int $status ) : string 
	{
		$statuses = self::get_status_array();
		return isset($statuses[$status]) ? $statuses[$status] : '';
	}

	//@TODO: user_mail, booking_mail, email...!?
	function __get( string $var ) : mixed
	{
		switch ($var) {
		
			case 'booking_status':
				return ($this->booking_status == self::PENDING && !get_option('dbem_bookings_approval') ) ? 1 : $this->booking_status;
			default:
				return null;
		}
	    
	}

	function load_request(\WP_REST_Request $request) : bool 
	{
		if(!$this->event_id) {
			$this->event_id = isset($request['event_id']) ? absint($request['event_id']) : 0;
		}

		$registration = $request['registration'];

		foreach([ 'first_name', 'last_name', 'user_email' ] as $key) {
			$this->booking_meta['registration'][$key] = $registration[$key];
			unset($registration[$key]);
		}

		$this->booking_mail = $this->booking_meta['registration']['user_email'];
		$this->booking_meta['booking'] = $registration;
		$this->booking_meta['attendees'] = $request['attendees'];
		$this->booking_meta['gateway'] = $request['gateway'];
		if( !empty($request['coupon']) ) {
			$this->booking_meta['coupon_code'] = $request['coupon'];
		}

		if( isset($request['donation']) && floatval($request['donation'] > 0) ){
			$this->booking_donation = floatval($request['donation']);
		}
		

		if( !empty($request['data_privacy_consent']) ){
			$this->booking_meta['consent'] = true;
		}

		$this->booking_date = new DateTime();
		$this->booking_spaces = count($request['attendees']);
		$this->get_status($request);
		return true;
	}

	public function get_first_name() : string 
	{
		return $this->booking_meta['registration']['first_name'] ?? "";
	}

	public function get_last_name() : string 
	{
		return $this->booking_meta['registration']['last_name'] ?? "";
	}

	public function get_email() : string 
	{
		return $this->booking_meta['registration']['user_email'] ?? "";
	}

	public function get_full_name() : string 
	{
		return $this->get_first_name() . ' ' . $this->get_last_name();
	}
	
	public function __set( string $property, mixed $value ) : void 
	{
		if( $property == 'timestamp' ){
			if( $this->date() !== false ) $this->date()->setTimestamp($value);
		}elseif( $property == 'language' ){
			$this->booking_meta['lang'] = $value;
		}else{
			$this->$property = $value;
		}
	}
	
	public function __isset( $property ) : bool 
	{
		if( $property == 'timestamp' ) return $this->date()->getTimestamp() > 0;
		if( $property == 'language' ) return !empty($this->booking_meta['lang']);
		return  isset($this->$property);
	}
	
	public function __sleep() : array 
	{
		$array = array('booking_id','event_id','booking_mail','booking_price','booking_spaces','booking_comment','booking_status','booking_donation','booking_meta','notes','booking_date','feedback_message','errors','mails_sent','custom','previous_status','status_array','manage_override');
		if( !empty($this->bookings) ) $array[] = 'bookings'; // EM Pro backwards compatibility
		return apply_filters('em_booking_sleep', $array, $this);
	}


	public function get_attendees() : array 
	{
		$result = [];
		foreach($this->booking_meta['attendees'] as $ticket_id => $attendees){
			foreach($attendees as $attendee) {
				array_push($result, ["ticket_id" => $ticket_id, "fields" => $attendee]);
			}
		}

		return $result;
	}



	public function date() : DateTime
	{
		if ($this->booking_date instanceof \DateTime) {
			return $this->booking_date;
		}
		
		return new DateTime("1st January 1970");
	}
	
	
	
	/**
	 * Saves the booking into the database, whether a new or existing booking
	 * @param bool $mail whether or not to email the user and contact people
	 * @return boolean
	 */
	function save(bool $mail = true) : bool 
	{
		global $wpdb;
		$table = EM_BOOKINGS_TABLE;
		do_action('em_booking_save_pre', $this);
		
		if (current_user_can('edit_published_posts')) {
			$this->feedback_message = __('Forbidden!', 'events');
			$this->errors[] = sprintf(__('You cannot manage this %s.', 'events'), __('Booking', 'events'));
			return apply_filters('em_booking_save', false, $this, false);
		}

		// Update prices, spaces
		$this->get_booked_spaces(true);
		$this->booking_price = $this->get_price();
		
		// Prepare data for saving
		$data = $this->to_array();
		$data['booking_meta'] = json_encode($this->booking_meta);
		$data_types = $this->get_types($data);
		
		// Save or update booking
		if ($this->booking_id) {
			$result = $wpdb->update($table, $data, ['booking_id' => $this->booking_id], $data_types) !== false;
			$this->feedback_message = __('Changes saved', 'events');
			if(!$result) $this->feedback_message = __('There was a problem UPDATING the booking.', 'events');
		} else {
			$data['booking_date'] = $this->booking_date->format('Y-m-d H:i:s');
			$data_types[] = '%s';
			$result = $wpdb->insert($table, $data, $data_types);
			$this->booking_id = $wpdb->insert_id;
			$this->feedback_message = __('Your booking has been recorded', 'events');
			if(!$result) $this->feedback_message = __('There was a problem SAVING the booking.', 'events');
		}

		

			// Apply filters and possibly send email
		//$this->compat_keys();
		$return = apply_filters('em_booking_save', count($this->errors) === 0, $this, (bool)$this->booking_id);
		
		if (count($this->errors) === 0 && $mail) {
			$this->email();
		}
		
		return $return;

	}

	function to_array(bool $sql_compatible = false) : array {
		$array = [];
		foreach ( $this->fields as $key => $val ) {
			if(!$sql_compatible) {
				$array[$key] = $this->$key;
				continue;
			}

			if ( !empty($this->$key) || $this->$key === 0 || $this->$key === '0' || empty($val['null']) ) {
				$array[$key] = $this->$key;
			} elseif ( $this->$key === null && !empty($val['null']) ) {
				$array[$key] = null;
			}
		}
		return $array;
	}
		
	
		/**
		 * Function to retreive wpdb types for all fields, or if you supply an assoc array with field names as keys it'll return an equivalent array of wpdb types
		 * @param array $array
		 * @return array:
		 */
		function get_types($array = array()){
			$types = array();
			if( count($array)>0 ){
				//So we look at assoc array and find equivalents
				foreach ($array as $key => $val){
					$types[] = $this->fields[$key]['type'];
				}
			}else{
				//Blank array, let's assume we're getting a standard list of types
				foreach ($this->fields as $field){
					$types[] = $field['type'];
				}
			}
			return apply_filters('em_object_get_types', $types, $this, $array);
		}	
		

	
	


	/**
	 * Load a record into this object by passing an associative array of table criteria to search for.
	 * Returns boolean depending on whether a record is found or not. 
	 * @param $search
	 * @return boolean
	 */
	function get($search) : bool 
	{
		global $wpdb;
		$conds = array(); 
		foreach($search as $key => $value) {
			if( array_key_exists($key, $this->fields) ){
				$value = esc_sql($value);
				$conds[] = "`$key`='$value'";
			} 
		}
		$sql = "SELECT * FROM ". EM_BOOKINGS_TABLE ." WHERE " . implode(' AND ', $conds) ;
		$result = $wpdb->get_row($sql, ARRAY_A);
		if($result){
			$this->from_array($result);
			return true;
		}

		return false;
	}


	public static function get_available_states() : array {
		$statuses = array(
			'all' => array('label'=>__('All','events'), 'search'=>false),
			'pending' => array('label'=>__('Pending','events'), 'search'=>0),
			'confirmed' => array('label'=>__('Confirmed','events'), 'search'=>1), 
			'cancelled' => array('label'=>__('Cancelled','events'), 'search'=>3),
			'rejected' => array('label'=>__('Rejected','events'), 'search'=>2),
			'needs-attention' => array('label'=>__('Needs Attention','events'), 'search'=>array(0)),
			'incomplete' => array('label'=>__('Incomplete Bookings','events'), 'search'=>array(0))
		);	

		if( !get_option('dbem_bookings_approval') ){
			unset($statuses['pending']);
			unset($statuses['incomplete']);
			$statuses['confirmed']['search'] = array(0,1);
		}
		
		return apply_filters('em_booking_statuses', $statuses);
	}

	function get_status_icon () {
		$icons = [
			'pending',
			'check_circle',
			'check_circle',
			'block',
			'pan_tool',
			'overview',
			'overview',
			'credit_card_clock',
			'overview',
		];
		return $icons[$this->booking_status];
	}

	function get_booking_url() : string 
	{
		if( $this->booking_id == 0 ) return $this->get_admin_url();
		return add_query_arg(['booking_id'=>$this->booking_id, 'em_ajax'=>null, 'em_obj'=>null], $this->get_admin_url());
	}

	private function validate_ticket_availability() : bool {
		$attendees = $this->booking_meta['attendees'] ?? [];
		$valid = true;
	
		foreach( $attendees as $ticket_id => $group ) {
			$ticket = \Contexis\Events\Models\Ticket::get_by_id($this->event_id, $ticket_id);
	
			if( !$ticket ) {
				$this->errors[] = sprintf(__('Ticket with ID %s does not exist.', 'events'), $ticket_id);
				$valid = false;
				continue;
			}
	
			if( !$ticket->is_available() ) {
				$message = get_option(
					'dbem_booking_feedback_ticket_unavailable',
					sprintf(__('The ticket "%s" is no longer available.', 'events'), $ticket->ticket_name)
				);
				$this->errors[] = $message;
				$valid = false;
			}
		}
	
		return $valid;
	}
	
	function validate( bool $override_availability = false ) : bool
	{
		if( $this->booking_spaces == 0 ){
			$this->errors[] = __('You must request at least one space to book an event.','events');
		}

		$result = true;

		if( !$override_availability ){
			// are bookings even available due to event and ticket cut-offs/restrictions? This is checked earlier in booking processes, but is relevant in checkout/cart situations where a previously-made booking is validated just before checkout
			if( $this->get_event()->get_rsvp_end()->getTimestamp() < time() ){
				$result = false;
				$this->errors[] = __( 'Bookings have closed (e.g. event has started).', 'events');
			}
			if( !$this->validate_ticket_availability() ) return false;
			//is there enough space overall?
			if( $this->get_event()->get_available_spaces() < $this->get_booked_spaces() ){
				$result = false;
				$this->errors[] = get_option('dbem_booking_feedback_full');
			}
		}

		if( $this->get_event()->get_available_spaces() < $this->get_booked_spaces() ){
			$result = false;
			$this->errors[] = __('You cannot book more spaces than are available.','events');
		}
		return apply_filters('em_booking_validate',$result, $this);
	}

	function get_payment_info() 
	{
		return GatewayService::get_gateway($this->booking_meta['gateway'])->get_payment_info($this);
	}

	function get_rest_fields() {
		return [
			'date' => $this->booking_date,
			'id' => $this->booking_id,
			'status' => $this->booking_status,
			'status_array' => self::get_status_array(),
			'price' => $this->get_price(),
			'donation' => $this->booking_donation,
			'paid' => $this->get_price_summary_array(),
			'gateway' => $this->booking_meta['gateway'],
			'coupon' => isset($this->booking_meta['coupon']) ? $this->booking_meta['coupon'] : null,
			'note' => isset($this->booking_meta['note']) ? $this->booking_meta['note'] : null,
		];
	}
	
	/**
	 * Get the total number of spaces booked in THIS booking. Setting $force_refresh to true will recheck spaces, even if previously done so.
	 * @param boolean $force_refresh
	 * @return int
	 */
	function get_booked_spaces( bool $force_refresh = false ) : int {
		if ( $this->booking_spaces == 0 || $force_refresh ) {
			$spaces = 0;
	
			if ( !empty($this->booking_meta['attendees']) && is_array($this->booking_meta['attendees']) ) {
				foreach ( $this->booking_meta['attendees'] as $attendee_group ) {
					if ( is_array($attendee_group) ) {
						$spaces += count($attendee_group);
					}
				}
			}
	
			$this->booking_spaces = $spaces;
		}
	
		return apply_filters('em_booking_get_spaces', $this->booking_spaces, $this);
	}
	
	
	/* Price Calculations */
	
	/**
	 * Gets the total price for this whole booking, including any discounts and any other additional items. In other words, what the person has to pay or has supposedly paid.
	 * This price shouldn't change once established, unless there's any alteration to the booking itself that'd affect the price, such as a change in ticket numbers, discount, etc.
	 * @param boolean $format
	 * @return double|string
	 */
	function get_price() : float 
	{
		//if( $this->booking_price !== null ) return $this->booking_price;
		$price = $this->get_price_base();
		$price -= $this->get_price_adjustments_amount('discounts');
		$price += $this->get_price_adjustments_amount('donation');
		$this->booking_price = $price;
		return round($this->booking_price,2);
	}
	
	public function get_price_base(): float {
		$total = 0.0;
		foreach ( $this->booking_meta['attendees'] as $ticket_id => $attendees ) {
			$ticket = \Contexis\Events\Models\Ticket::get_by_id( $this->event_id, $ticket_id );
			if ( $ticket ) {
				$total += count( $attendees ) * floatval( $ticket->ticket_price );
			}
		}
		return $total;
	}

	/**
	 * Returns an array of discounts to be applied to a booking. Here is an example of an array item that is expected:
	 * array('name' => 'Name of Discount', 'type'=>'% or #', 'amount'=> 0.00, 'desc' => 'Comments about discount', 'data' => 'any info for hooks to use' );
	 * About the array keys:
	 * type - # means a fixed amount of discount, % means a percentage off the base price
	 * amount - if type is a percentage, it is written as a number from 0-100, e.g. 10 = 10%
	 * data - any data to be stored that can be used by actions/filters
	 * @param string $type The type of adjustment you would like to retrieve. This would normally be 'discounts' or 'donation'.
	 * @return array
	 */
	function get_price_adjustments( string $type ){
		$adjustments = array();

		if( $type == 'donation') {
			$adjustments[] = array('name' => __('Donation', 'events'), 'type' => '#', 'amount' => $this->booking_donation, 'desc' => __('Donation', 'events'));
		}
		
		return apply_filters('em_booking_get_price_adjustments', $adjustments, $type, $this);
	}
	
	/**
	 * Returns a numerical amount to adjust the price by, in the context of a certain type.
	 * This will be a positive number whether or not this is to be added or subtracted from the price.
	 * @param string $type The type of adjustment to get, which would normally be 'discounts' or 'surcharges'
	 * @param float $price Price relative to be adjusted.
	 * @return float
	 */
	function get_price_adjustments_amount( string $type ){
		$adjustments = $this->get_price_adjustments_summary($type);
		
		$adjustment_amount = 0;
		foreach($adjustments as $adjustment){
			$adjustment_amount += $adjustment['amount_adjusted'];
		}
		return $adjustment_amount;
	}
	
	/**
	 * Provides an array summary of adjustments to make to the price, in the context of a certain type.
	 * @param string $type The type of adjustment to get, which would normally be 'discounts' or 'surcharges'
	 * @param float $price Price to calculate relative to adjustments. If not supplied or if $pre_or_post is 'both', price is automatically obtained from booking instance. 
	 * @return array
	 */
	function get_price_adjustments_summary( string $type ) : array{
		
		$adjustments = $this->get_price_adjustments($type);
		
		$price = $this->get_price_base();
		

		$adjustment_summary = [];

		foreach($adjustments as $adjustment){
			if(empty($adjustment['amount']) || empty($adjustment['type'])) continue;
			$description = !empty($adjustment['desc']) ? $adjustment['desc'] : '';
			$adjustment_summary_item = array('name' => $adjustment['name'], 'desc' => $description, 'adjustment'=>'0', 'amount_adjusted'=>0);
			$adjustment_summary_item['amount_adjusted'] = $adjustment['type'] == '%' ? round($price * ($adjustment['amount']/100),2) : round($adjustment['amount'],2);
			$adjustment_summary_item['adjustment'] = $adjustment['type'] == '%' ? number_format($adjustment['amount'],2).'%' : Price::format($adjustment['amount']);
			$adjustment_summary_item['amount'] = Price::format($adjustment_summary_item['amount_adjusted']);	
			$adjustment_summary[] = $adjustment_summary_item;
		}
		
		return $adjustment_summary;
	}

	/**
	 * When generating totals at the bottom of a booking, this creates a useful array for displaying the summary in a meaningful way. 
	 */
	function get_price_summary_array(){
	    $summary = array();
	    $summary['total_base'] = $this->get_price_base();
	    $summary['discounts'] = $this->get_price_adjustments_amount('discounts');
	    $summary['donation'] = $this->get_price_adjustments_amount('donation');
	    $summary['total'] =  $this->get_price();
	    return $summary;
	}
	
	/**
	 * Returns the amount paid for this booking. By default, a booking is considered either paid in full or not at all depending on whether the booking is confirmed or not.
	 * @param boolean $format If set to true a currency-formatted string value is returned
	 * @return string|float
	 */
	function get_total_paid( ) : float {
		$status = ($this->booking_status == 0 && !get_option('dbem_bookings_approval') ) ? 1:$this->booking_status;
		$total = $status ? $this->get_price() : 0;
		$total = apply_filters('em_booking_get_total_paid', $total, $this);
		return floatval($total);
	}
	
	
	/* Get Objects linked to booking */
	
	/**
	 * Gets the event this booking belongs to and saves a reference in the event property
	 * @return Event
	 */
	function get_event() : Event {
		if($this->event_id == 0) return new Event();
		return Event::get_by_id($this->event_id);
	}
	
	/**
	 * Gets the ticket object this booking belongs to, saves a reference in ticket property
	 * @return Tickets
	 */
	function get_tickets() : TicketCollection {
		return TicketCollection::find_by_booking($this);
	}

	
	function get_status() : string 
	{
		$status = ($this->booking_status == self::PENDING && !get_option('dbem_bookings_approval') ) ? self::APPROVED : $this->booking_status;
		return apply_filters('em_booking_get_status', self::get_status_label($status), $this);
	}
	
	function delete() : bool 
	{
		global $wpdb;
		$result = false;
		if(!current_user_can('editor')) {
			$this->errors[] = __('You don\'t have the necessary rights to delete bookings', 'events');
			return false;
		}
			
		$sql = $wpdb->prepare("DELETE FROM ". EM_BOOKINGS_TABLE . " WHERE booking_id=%d", $this->booking_id);
		
		$result = $wpdb->query( $sql );

		if(!$result){
			$this->errors[] = sprintf(__('Booking could not be deleted', 'events'), __('Booking','events'));
			return false;
		}
		
		$this->booking_status = self::DELETED;
		$this->feedback_message = sprintf(__('Booking deleted', 'events'));
		
		do_action('em_bookings_deleted', $result, array($this->booking_id), $this);
		return apply_filters('em_booking_delete', ( $result !== false ), $this);
	}
	
	function cancel($email = true) : bool 
	{
		return $this->set_status(self::CANCELLED, $email);
	}
	
	function approve($email = true, $ignore_spaces = false) : bool {
		return $this->set_status(self::APPROVED, $email, $ignore_spaces);
	}	

	function reject($email = true) : bool 
	{
		return $this->set_status(self::REJECTED, $email);
	}	
	
	function unapprove($email = true) : bool 
	{
		return $this->set_status(self::PENDING, $email);
	}
	
	/**
	 * Change the status of the booking. This will save to the Database too. 
	 * @param int $status
	 * @return boolean
	 */
	function set_status(int $status, bool $email = true, $ignore_spaces = false) : bool 
	{
		global $wpdb;
		$action_string = strtolower(self::get_status_label($status)); 
		
		if(!$ignore_spaces && $status == 1){
			if( !$this->is_reserved() && $this->get_event()->get_available_spaces() < $this->get_booked_spaces() && !get_option('dbem_bookings_approval_overbooking') ){
				$this->feedback_message = sprintf(__('Not approved, spaces full.','events'), $action_string);
				$this->errors[] = $this->feedback_message;
				return apply_filters('em_booking_set_status', false, $this);
			}
		}
		$this->previous_status = $this->booking_status;
		$this->booking_status = $status;
		$result = $wpdb->query($wpdb->prepare('UPDATE '.EM_BOOKINGS_TABLE.' SET booking_status=%d WHERE booking_id=%d', array($status, $this->booking_id)));
		if($result !== false){
			$this->feedback_message = sprintf(__('Booking %s.','events'), $action_string);
			$result = apply_filters('em_booking_set_status', $result, $this); // run the filter before emails go out, in case others need to hook in first
			if( $result && $email && $this->previous_status != $this->booking_status ){ //email if status has changed
				if( $this->email() ){
				    if( $this->mails_sent > 0 ){
				        $this->feedback_message .= " ".__('Email Sent.','events');
				    }
				}else{
					//extra errors may be logged by email() in EM_Object
					$this->feedback_message .= ' <span style="color:red">'.__('ERROR : Email Not Sent.','events').'</span>';
					$this->errors[] = __('ERROR : Email Not Sent.','events');
				}
			}
		}else{
			//errors should be logged by save()
			$this->feedback_message = sprintf(__('Booking could not be %s.','events'), $action_string);
			$this->errors[] = sprintf(__('Booking could not be %s.','events'), $action_string);
			$result =  apply_filters('em_booking_set_status', false, $this);
		}
		return $result;
	}
	
	/**
	 * Returns true if booking is reserving a space at this event, whether confirmed or not 
	 */
	function is_reserved(){
	    $result = false;
	    if( $this->booking_status == self::PENDING && get_option('dbem_bookings_approval_reserved') ){
	        $result = true;
	    }elseif( $this->booking_status == self::PENDING && !get_option('dbem_bookings_approval') ){
	        $result = true;
	    }elseif( $this->booking_status == self::APPROVED ){
	        $result = true;
	    }
	    return apply_filters('em_booking_is_reserved', $result, $this);
	}
	
	/**
	 * Returns true if booking is associated with a non-registered user, i.e. booked as a guest 'no user mode'.
	 * @return mixed
	 */
	function is_no_user(){
		return true;
	}
	
	/**
	 * Returns true if booking is either pending or reserved but not confirmed (which is assumed pending) 
	 */
	function is_pending() : bool
	{
		$result = ($this->is_reserved() || $this->booking_status == 0) && $this->booking_status != 1;
	    return apply_filters('em_booking_is_pending', $result, $this);
	}
	
	

	function get_admin_url() : string
	{
		return is_admin() ? EventPost::get_admin_url(). "&page=events-bookings&event_id=".$this->event_id."&booking_id=".$this->booking_id : "";
	}
	
	function email_send($subject, $body, $email, $attachments = array()){
		
		global $EM_Mailer;
		if( !empty($subject) ){
			if( !is_object($EM_Mailer) ){
				$EM_Mailer = new \EM_Mailer();
			}
			if( !$EM_Mailer->send($subject,$body,$email, $attachments) ){
				if( is_array($EM_Mailer->errors) ){
					foreach($EM_Mailer->errors as $error){
						$this->errors[] = $error;
					}
				}else{
					$this->errors[] = $EM_Mailer->errors;
				}
				return false;
			}
		}
		return true;
	}

	function email( bool $email_admin = true, bool $force_resend = false, bool $email_attendee = true ) : bool
	{
		$result = true;
		$this->mails_sent = 0;
		
		
		//Make sure event matches booking, and that booking used to be approved.
		if( $this->booking_status !== $this->previous_status || $force_resend ){

			do_action('em_booking_email_before_send', $this);
			//get event info and refresh all bookings
			$event = $this->get_event(); //We NEED event details here.
			$event->get_bookings(true); //refresh all bookings
			//messages can be overridden just before being sent
			$msg = $this->email_messages();

			//Send user (booker) emails
			if( !empty($msg['user']['subject']) && $email_attendee ){
				$msg['user']['subject'] = BookingView::render($this, $msg['user']['subject'], 'raw');
				$msg['user']['body'] = BookingView::render($this, $msg['user']['body'], 'email');
				$attachments = array();
				if( !empty($msg['user']['attachments']) && is_array($msg['user']['attachments']) ){
					$attachments = $msg['user']['attachments'];
				}
				
				if( !$this->email_send( $msg['user']['subject'], $msg['user']['body'], $this->booking_mail, $attachments) ){
					$result = false;
				}else{
					$this->mails_sent++;
				}
			}
			
			//Send admin/contact emails if this isn't the event owner or an events admin
			if( $email_admin && !empty($msg['admin']['subject']) ){ //emails won't be sent if admin is logged in unless they book themselves
				//get admin emails that need to be notified, hook here to add extra admin emails
				$admin_emails = str_replace(' ','',get_option('dbem_bookings_notify_admin'));
				$admin_emails = apply_filters('em_booking_admin_emails', explode(',', $admin_emails), $this); //supply emails as array
				if( get_option('dbem_bookings_contact_email') == 1 && !empty($event->get_contact()->user_email) ){
				    //add event owner contact email to list of admin emails
				    $admin_emails[] = $event->get_contact()->user_email;
				}
				foreach($admin_emails as $key => $email){ if( !is_email($email) ) unset($admin_emails[$key]); } //remove bad emails
				//proceed to email admins if need be
				if( !empty($admin_emails) ){
					//Only gets sent if this is a pending booking, unless approvals are disabled.
					$msg['admin']['subject'] = BookingView::render($this, $msg['admin']['subject'],'raw');
					$msg['admin']['body'] = BookingView::render($this, $msg['admin']['body'], 'email');
					$attachments = array();
					if( !empty($msg['admin']['attachments']) && is_array($msg['admin']['attachments']) ){
						$attachments = $msg['admin']['attachments'];
					}
					//email admins
						if( !$this->email_send( $msg['admin']['subject'], $msg['admin']['body'], $admin_emails, $attachments) && current_user_can('manage_options') ){
							$this->errors[] = __('Confirmation email could not be sent to admin. Registrant should have gotten their email (only admin see this warning).','events');
							$result = false;
						}else{
							$this->mails_sent++;
						}
				}
			}
			do_action('em_booking_email_after_send', $this);
		}
		return apply_filters('em_booking_email', $result, $this, $email_admin, $force_resend, $email_attendee);
		//TODO need error checking for booking mail send
	}	
	
	function email_messages() : array
	{
		$msg = array( 'user'=> array('subject'=>'', 'body'=>''), 'admin'=> array('subject'=>'', 'body'=>'')); //blank msg template			
		//admin messages won't change whether pending or already approved
	    switch( $this->booking_status ){
	    	case 0:
	    	case 5: //TODO remove offline status from here and move to pro
	    		$msg['user']['subject'] = get_option('dbem_bookings_email_pending_subject');
	    		$msg['user']['body'] = get_option('dbem_bookings_email_pending_body');
	    		//admins should get something (if set to)
	    		$msg['admin']['subject'] = get_option('dbem_bookings_contact_email_pending_subject');
	    		$msg['admin']['body'] = get_option('dbem_bookings_contact_email_pending_body');
	    		break;
	    	case 1:
	    		$msg['user']['subject'] = get_option('dbem_bookings_email_confirmed_subject');
	    		$msg['user']['body'] = get_option('dbem_bookings_email_confirmed_body');
	    		//admins should get something (if set to)
	    		$msg['admin']['subject'] = get_option('dbem_bookings_contact_email_confirmed_subject');
	    		$msg['admin']['body'] = get_option('dbem_bookings_contact_email_confirmed_body');
	    		break;
	    	case 2:
	    		$msg['user']['subject'] = get_option('dbem_bookings_email_rejected_subject');
	    		$msg['user']['body'] = get_option('dbem_bookings_email_rejected_body');
	    		//admins should get something (if set to)
	    		$msg['admin']['subject'] = get_option('dbem_bookings_contact_email_rejected_subject');
	    		$msg['admin']['body'] = get_option('dbem_bookings_contact_email_rejected_body');
	    		break;
	    	case 3:
	    		$msg['user']['subject'] = get_option('dbem_bookings_email_cancelled_subject');
	    		$msg['user']['body'] = get_option('dbem_bookings_email_cancelled_body');
	    		//admins should get something (if set to)
	    		$msg['admin']['subject'] = get_option('dbem_bookings_contact_email_cancelled_subject');
	    		$msg['admin']['body'] = get_option('dbem_bookings_contact_email_cancelled_body');
	    		break;
	    }
	    return apply_filters('em_booking_email_messages', $msg, $this);
	}

	static function booking_enabled() : array
	{
		$enabled = [
			'is_enabled' => true,
			'message' => ''
			
		];

		$active_gateways = GatewayService::active_gateways();

		if( count($active_gateways) == 0 ){
			$enabled['is_enabled'] = false;
			$enabled['message'] = __('No payment gateways are enabled. Please enable at least one payment gateway.', 'events');
			return $enabled;
		}

		if( array_key_exists('offline', $active_gateways) && (!get_option('em_offline_iban', false) || !get_option('em_offline_beneficiary', false) || !get_option('em_offline_bank', false)) ) {
			$enabled['is_enabled'] = false;
			$missing_fields = array();
			if( !get_option('em_offline_iban', false) ) $missing_fields[] = __('IBAN', 'events');
			if( !get_option('em_offline_beneficiary', false) ) $missing_fields[] = __('Beneficiary', 'events');
			if( !get_option('em_offline_bank', false) ) $missing_fields[] = __('Bank', 'events');
			$enabled['message'] = __('Offline Payment is not configured correctly. The following fields are missing:', 'events') . ' ' . implode(', ', $missing_fields) . __('. Please check your gateway settings.', 'events');
			return $enabled;
		}

		if( array_key_exists('mollie', $active_gateways) && !get_option('em_mollie_api_key', false) ) {
			$enabled['is_enabled'] = false;
			$enabled['message'] = __('Mollie API Key is not set. Please check your gateway settings.', 'events');
			return $enabled;
		}

		return $enabled;
	}

}