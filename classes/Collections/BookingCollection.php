<?php

namespace Contexis\Events\Collections;

use Contexis\Events\Models\Booking;
use Contexis\Events\Collections\TicketCollection;
use Contexis\Events\Models\Ticket;
use Contexis\Events\Models\Event;
use Contexis\Events\EM_Object;
use Contexis\Events\Utilities\SQLHelper;
use IteratorAggregate;
use Countable;

class BookingCollection implements IteratorAggregate, Countable {
	
	public array $bookings = [];

	var $tickets;
	
	public int $event_id = 0;
	
	var $spaces;
	
	var $translated;

	public string $feedback_message = '';
	
	public static $force_registration;
	
	public static $disable_restrictions = false;

	public array $errors = array();
	
	protected $booked_spaces;
	protected $pending_spaces;
	protected $available_spaces;

	public static function from_event_id(int $event_id) : BookingCollection {
		$instance = new self();
		$instance->event_id = $event_id;
		$instance->load();
		return $instance;
	}

	/**
	 * Creates a BookingCollection from an array of Booking objects
	 * @param array<Booking> $bookings
	 * @return BookingCollection
	 */
	public static function from_bookings(array $bookings) : BookingCollection {
		$instance = new self();
		foreach( $bookings as $booking ){
			$instance->bookings[] = $booking;
		}
		return $instance;
	}

	/**
	 * Creates a BookingCollection from an array of booking IDs
	 * @param array<int> $booking_ids
	 * @return BookingCollection
	 */
	public static function from_booking_ids(array $booking_ids) : BookingCollection {
		$instance = new self();
		foreach( $booking_ids as $booking_id ){
			$instance->bookings[] = Booking::get_by_id(absint($booking_id));
		}
		return $instance;
	}

	public static function find() {
		return new self();
	}	
	
	public function __isset( $prop ){
		if( $prop == 'bookings' ){
			return !empty($this->bookings);
		}
		return isset($this->$prop);
	}
	
	public function load(){
		if(!empty($this->bookings)) return $this->bookings;

		global $wpdb;
		
		if( $this->event_id > 0 ){
			$sql = $wpdb->prepare("SELECT booking_id FROM ". EM_BOOKINGS_TABLE ." WHERE event_id = %d ORDER BY booking_date", $this->event_id);
			$booking_ids = $wpdb->get_row($sql);
		}

		foreach ($booking_ids as $booking_id){
			$this->bookings[] = Booking::get_by_id($booking_id);
		}
		
		return $this->bookings;
	}
	
	function add( Booking $booking ){
		
		//Save the booking
		$emailSent = false;
		//set status depending on approval settings
		if( empty($booking->booking_status) ){ //if status is not set, give 1 or 0 depending on approval settings
			$booking->booking_status = get_option('dbem_bookings_approval') ? Booking::PENDING : Booking::APPROVED;
		}
		$result = $booking->save(false);
		if($result){ 
			//Success
		    do_action('em_bookings_added', $booking);
			if( $this->bookings === null ) $this->bookings = array();
			$this->bookings[] = $booking;
			$emailSent = $booking->email();
			if( get_option('dbem_bookings_approval') == 1 && $booking->booking_status == Booking::PENDING){
				$this->feedback_message = get_option('dbem_booking_feedback_pending');
			}else{
				$this->feedback_message = get_option('dbem_booking_feedback');
			}
			if(!$emailSent){
				$booking->email_not_sent = true;
				$this->feedback_message .= ' '.__('However, there were some problems whilst sending confirmation emails to you and/or the event contact person. You may want to contact them directly and letting them know of this error.', 'events');
				
			}
			return apply_filters('em_bookings_add', true, $booking);
		}else{
			//Failure
			$this->errors[] = "<strong>".__('Booking could not be created','events')."</strong><br />". implode('<br />', $booking->errors);
		}
		return apply_filters('em_bookings_add', false, $booking);
	}


	
	/**
	 * Smart event locator, saves a database read if possible. Note that if an event doesn't exist, a blank object will be created to prevent duplicates.
	 */
	public function get_event() : ?Event {
		if (!empty($this->event_id) && is_numeric($this->event_id)) {
			return Event::get_by_id($this->event_id);
		}
		
		if (!empty($this->bookings) && is_array($this->bookings)) {
			foreach ($this->bookings as $booking) {
				return Event::get_by_id($booking->event_id);
			}
		}
		
		return null;
	}
	
	function get_tickets( $force_reload = false ) : TicketCollection {
		if( !is_object($this->tickets) || $force_reload ){
			$this->tickets = TicketCollection::find_by_event_id($this->event_id);
		}else{
			$this->tickets->event_id = $this->event_id;
		}
		return apply_filters('em_bookings_get_tickets', $this->tickets, $this);
	}
	
	/**
	 * Returns Tickets object with available tickets
	 * @param boolean $include_member_tickets - if set to true, member-ony tickets will be considered available even if logged out
	 * @return Tickets
	 */
	function get_available_tickets( $include_member_tickets = false ){
		$tickets = array();
		
		foreach ($this->get_tickets() as $ticket){
			/* @var $ticket Ticket */
			if( $ticket->is_available() ){
				//within time range
				if( $ticket->get_available_spaces() > 0 ){
					$tickets[] = $ticket;
				}
			}
		}
		
		return apply_filters('em_bookings_get_available_tickets', $this->get_tickets(), $this);
	}

	
	
	/**
	 * Deprecated - was never used and therefore is deprecated, will always return an array() and will eventually be removed entirely.
	 * @return array
	 */
	function get_user_list(){
		return array();
	}
	
	/**
	 * Returns a boolean indicating whether this ticket exists in this bookings context.
	 * @return bool 
	 */
	function ticket_exists($ticket_id){
		$tickets = $this->get_tickets();
		foreach( $tickets->tickets as $ticket){
			
			if($ticket->ticket_id == $ticket_id){
				return apply_filters('em_bookings_ticket_exists',true, $ticket, $this);
			}
		}
		return apply_filters('em_bookings_ticket_exists',false, false,$this);
	}
	
	function has_space( $include_member_tickets = false ){
		return count($this->get_available_tickets( $include_member_tickets )->tickets) > 0;
	}
	
	/**
	 * Delete bookings on this id
	 * @return boolean
	 */
	function delete(){
		global $wpdb;
		$booking_ids = array();
		if( !empty($this->bookings) ){
			//get the booking ids tied to this event or preloaded into this object
			foreach( $this->bookings as $booking ){
				$booking_ids[] = $booking->booking_id;
			}
			$result = true;
			if( count($booking_ids) > 0 ){
				//Delete bookings and ticket bookings
				$result = $wpdb->query("DELETE FROM ".EM_BOOKINGS_TABLE." WHERE booking_id IN (".implode(',',$booking_ids).")");
			}
		}elseif( !empty($this->event_id) ){
			//faster way of deleting bookings for an event circumventing the need to load all bookings if it hasn't been loaded already
			$event_id = absint($this->event_id);
			$booking_ids = $wpdb->get_col("SELECT booking_id FROM ".EM_BOOKINGS_TABLE." WHERE event_id = '$event_id'");
			$result = $wpdb->query("DELETE FROM ".EM_BOOKINGS_TABLE." WHERE event_id = '$event_id'");
		}else{
			//we have not bookings loaded to delete, nor an event to delete bookings from, so bookings are considered 'deleted' since there's nothing ot delete
			$result = true;
		}
		do_action('em_bookings_deleted', $result, $booking_ids);
		return apply_filters('em_bookings_delete', $result !== false, $booking_ids, $this);
	}

	
	/**
	 * Will approve all supplied booking ids, which must be in the form of a numeric array or a single number.
	 * @param array|int $booking_ids
	 * @return boolean
	 */
	function approve( $booking_ids ){
		$this->set_status(1, $booking_ids);
		return false;
	}
	
	/**
	 * Will reject all supplied booking ids, which must be in the form of a numeric array or a single number.
	 * @param array|int $booking_ids
	 * @return boolean
	 */
	function reject( $booking_ids ){
		return $this->set_status(2, $booking_ids);
	}
	
	/**
	 * Will unapprove all supplied booking ids, which must be in the form of a numeric array or a single number.
	 * @param array|int $booking_ids
	 * @return boolean
	 */
	function unapprove( $booking_ids ){
		return $this->set_status(0, $booking_ids);
	}
	
	/**
	 * @param int $status
	 * @param array|int $booking_ids
	 * @param bool $send_email
	 * @param bool $ignore_spaces
	 * @return bool
	 */
	function set_status( $status, $booking_ids, $send_email = true, $ignore_spaces = false ){
		//FIXME status should work with instantiated object
		if( is_array($booking_ids) && !empty($booking_ids) && array_is_list($booking_ids) ){
			//Get all the bookings
			$results = array();
			$mails = array();
			foreach( $booking_ids as $booking_id ){
				$booking = Booking::get_by_id($booking_id);
				if( current_user_can('edit_others_events')	 ){
					$this->feedback_message = __('Bookings %s. Mails Sent.', 'events');
					return false;
				}
				$results[] = $booking->set_status($status, $send_email, $ignore_spaces);
			}
			if( !in_array('false',$results) ){
				$this->feedback_message = __('Bookings %s. Mails Sent.', 'events');
				return true;
			}else{
				//TODO Better error handling needed if some bookings fail approval/failure
				$this->feedback_message = __('An error occurred.', 'events');
				return false;
			}
		}elseif( is_numeric($booking_ids) || is_object($booking_ids) ){
			$booking = ( is_object($booking_ids) && $booking_ids instanceof Booking) ? $booking_ids : Booking::get_by_id($booking_ids);
			$result = $booking->set_status($status);
			$this->feedback_message = $booking->feedback_message;
			return $result;
		}
		return false;	
	}
	



	public static function get( $args = array() ) : BookingCollection {
		global $wpdb;
		$bookings_table = EM_BOOKINGS_TABLE;
		
		//We assume it's either an empty array or array of search arguments to merge with defaults			
		$args = self::get_default_search($args);
		$limit = ( $args['limit'] && is_numeric($args['limit'])) ? "LIMIT {$args['limit']}" : '';
		$offset = ( $limit != "" && is_numeric($args['offset']) ) ? "OFFSET {$args['offset']}" : '';
		
		//Get the default conditions
		$conditions = self::build_sql_conditions($args);
		//Put it all together
		$where = ( count($conditions) > 0 ) ? " WHERE " . implode ( " AND ", $conditions ):'';
		
		//Get ordering instructions
		$booking = new Booking();
		$accepted_fields = $booking->get_fields(true);
		$accepted_fields['date'] = 'booking_date';
		$orderby = self::build_sql_orderby($args, $accepted_fields);
		//Now, build orderby sql
		$orderby_sql = ( count($orderby) > 0 ) ? 'ORDER BY '. implode(', ', $orderby) : 'ORDER BY booking_date';
		//Selectors
		if( is_array($args['array']) ){
			$selectors = implode(',', $args['array']);
		}else{
			$selectors = '*';
		}
		
		$sql = $wpdb->prepare("SELECT booking_id FROM $bookings_table $where $orderby_sql $limit $offset");
		$results = $wpdb->get_col($sql);
		$results = (is_array($results)) ? $results:array();
		$bookings = array();
		foreach ( $results as $booking ){
			$bookings[] = Booking::from_booking_id($booking);
		}

		$bookings = BookingCollection::from_bookings($bookings);

		return $bookings;
	}
	
	/**
	 * Checks whether a booking being made should register user information as a booking from another user whilst an admin is logged in
	 * @return boolean
	 */
	public static function is_registration_forced(){
		return ( defined('EM_FORCE_REGISTRATION') || self::$force_registration );
	}
	

	


	public static function build_sql_conditions(array $args): array {
		$conditions = [];
	
		// Booking status
		if (isset($args['status'])) {
			if (is_numeric($args['status'])) {
				$conditions['status'] = 'booking_status = ' . (int) $args['status'];
			} elseif (is_array($args['status']) && array_is_list($args['status']) && count($args['status']) > 0) {
				$safe = array_map('intval', $args['status']);
				$conditions['status'] = 'booking_status IN (' . implode(',', $safe) . ')';
			} elseif (preg_match('/^([0-9],?)+$/', $args['status'])) {
				$conditions['status'] = 'booking_status IN (' . $args['status'] . ')';
			}
		}
	
		// Event = false → alles mit event_id != 0
		if (!isset($conditions['event']) && array_key_exists('event', $args) && $args['event'] === false) {
			$conditions['event'] = EM_BOOKINGS_TABLE . '.event_id != 0';
		}
	
		// Ticket filter
		if ( isset($args['ticket_id']) && is_numeric($args['ticket_id']) ) {
			if ( !current_user_can('edit_posts') ) return $conditions;
		
			$ticket_id = (int) $args['ticket_id'];
		
			// Statt SQL-Subquery: Filter später nach ticket_id
			$conditions['ticket_id_filter'] = $ticket_id;
		}

		if ( isset($conditions['ticket_id_filter']) ) {
			$ticket_id = $conditions['ticket_id_filter'];
			unset($conditions['ticket_id_filter']);
		
			$bookings = array_filter($bookings, function($booking) use ($ticket_id) {
				return !empty($booking->booking_meta['attendees'][$ticket_id]);
			});
		}
	
		return $conditions;
	}
	
	/* Overrides EM_Object method to apply a filter to result
	 */
	public static function build_sql_orderby( $args, $accepted_fields, $default_order = 'ASC' ){
		return apply_filters( 'em_bookings_build_sql_orderby', SQLHelper::build_sql_orderby($args, $accepted_fields, get_option('dbem_bookings_default_order','booking_date')), $args, $accepted_fields, $default_order );
	}
	
	public static function get_default_search(array $input = []): array {
		$defaults = [
			'status' => false,
			'person' => true,
			'ticket_id' => false,
			'array' => false,
			'limit' => 10,
			'page' => 1,
			'offset' => 0,
			'orderby' => ['booking_date'],
			'order' => 'ASC',
			'owner' => current_user_can('edit_others_events') ? false : get_current_user_id(),
		];
	
		$search = array_merge($defaults, $input);
	
		if (!empty($search['array'])) {
			$booking = new Booking();
			if (is_array($search['array'])) {
				$valid_fields = array_filter($search['array'], fn($field) => array_key_exists($field, $booking->fields));
				$search['array'] = !empty($valid_fields) ? array_values($valid_fields) : true;
			} elseif (is_string($search['array']) && array_key_exists($search['array'], $booking->fields)) {
				$search['array'] = [$search['array']];
			} else {
				$search['array'] = true;
			}
		}
	
		$search['limit'] = is_numeric($search['limit']) ? (int) $search['limit'] : $defaults['limit'];
		$search['page'] = is_numeric($search['page']) ? max(1, (int) $search['page']) : 1;
		$search['offset'] = ($search['page'] > 1 && $search['limit'] > 0) ? $search['limit'] * ($search['page'] - 1) : 0;
	
		$search['order'] = in_array(strtoupper($search['order']), ['ASC', 'DESC']) ? strtoupper($search['order']) : 'ASC';
		if (is_string($search['orderby'])) {
			$search['orderby'] = array_map('trim', explode(',', $search['orderby']));
		} elseif (!is_array($search['orderby'])) {
			$search['orderby'] = ['booking_date'];
		}
	
		return apply_filters('em_bookings_get_default_search', $search, $input, $defaults);
	}
	
	

	public function getIterator(): \Traversable {
        return new \ArrayIterator($this->bookings);
    }

	public function count(): int {
        return count($this->bookings);
    }
}
