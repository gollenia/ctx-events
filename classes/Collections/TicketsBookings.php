<?php

namespace Contexis\Events\Tickets;

use Contexis\Events\Model\Booking;
use Contexis\Events\Models\Ticket;
use Contexis\Events\Utilities\SQLHelper;
/**
 * Deals with the each ticket booked in a single booking
 * @author marcus
 *
 */
class TicketsBookings extends \EM_Object implements \Iterator, \Countable {
	
	/**
	 * Array of TicketBooking objects for a specific event
	 * @var array[TicketBooking]
	 */
	var $tickets_bookings = array();
	/**
	 * When adding existing booked tickets via add() with 0 spaces, they get slotted here for deletion during save() so they circumvent validation.
	 * @var array[TicketBooking]
	 */
	var $tickets_bookings_deleted = array();

	public Booking $booking;
	var $booking_id;
	/**
	 * This object belongs to this booking object
	 * @var Ticket
	 */
	var $ticket;
	var $spaces;
	var $price;
	
	/**
	 * Creates an Tickets instance, 
	 * @param mixed $object
	 */
	function __construct( $object = false ){
		global $wpdb;
		if($object){
			if( is_object($object) && get_class($object) == "Booking"){
				$this->booking = $object;
				$sql = "SELECT * FROM ". EM_TICKETS_BOOKINGS_TABLE ." WHERE booking_id ='{$this->booking->booking_id}'";
			}elseif( is_object($object) && get_class($object) == "EM_Ticket"){
				$this->ticket = $object;
				$sql = "SELECT * FROM ". EM_TICKETS_BOOKINGS_TABLE ." WHERE ticket_id ='{$this->ticket->ticket_id}'";
			}elseif( is_numeric($object) ){
				$sql = "SELECT * FROM ". EM_TICKETS_BOOKINGS_TABLE ." WHERE booking_id ='{$object}'";
			}
			$tickets_bookings = $wpdb->get_results($sql, ARRAY_A);
			//Get tickets belonging to this tickets booking.
			foreach ($tickets_bookings as $item){
				$ticket_booking = new TicketBooking($item);
				$ticket_booking->booking = $this->booking; //save some calls
				$this->tickets_bookings[$item['ticket_id']] = $ticket_booking;
			}
		}
		do_action('em_tickets_bookings',$this, $object);
	}
	
	/**
	 * Saves the ticket bookings for this booking into the database, whether a new or existing booking
	 * @return boolean
	 */
	function save(){
		do_action('em_tickets_bookings_save_pre',$this);
		//save/update tickets
		foreach( $this->tickets_bookings as $ticket_booking ){
			$result = $ticket_booking->save();
			if(!$result){
				$this->errors = array_merge($this->errors, $ticket_booking->get_errors());
			}
		}
		//delete old tickets if set to 0 in an update
		foreach($this->tickets_bookings_deleted as $ticket_booking ){
			$result = $ticket_booking->delete();
			if(!$result){
				$this->errors = array_merge($this->errors, $ticket_booking->get_errors());
			}
		}
		//return result
		if( count($this->errors) > 0 ){
			$this->feedback_message = __('There was a problem saving the booking.', 'events');
			$this->errors[] = __('There was a problem saving the booking.', 'events');
			return apply_filters('em_tickets_bookings_save', false, $this);
		}
		return apply_filters('em_tickets_bookings_save', true, $this);
	}
	
	/**
	 * Add a booking into this event object, checking that there's enough space for the event
	 * @param TicketBooking $ticket_booking
	 * @param boolean $override
	 * @return boolean
	 */
	function add( $ticket_booking, $override = false ){ //note, $override was a quick fix, not necessarily permanent, so don't depend on it just yet
		global $wpdb,$EM_Mailer;
		//Does the ticket we want to book have enough spaeces? 
		if ( $override || $ticket_booking->get_ticket()->get_available_spaces() >= $ticket_booking->get_spaces() ) {  
			$ticket_booking_key = $this->has_ticket($ticket_booking->ticket_id);
			$this->price = 0; //so price calculations are reset
			if( $ticket_booking_key !== false && is_object($this->tickets_bookings[$ticket_booking->ticket_id]) ){
				if( $ticket_booking->get_spaces() > 0 ){
					//previously booked ticket, so let's just reset spaces/prices and replace it
					$this->tickets_bookings[$ticket_booking->ticket_id]->ticket_booking_spaces = $ticket_booking->get_spaces();
					$this->tickets_bookings[$ticket_booking->ticket_id]->ticket_booking_price = $ticket_booking->get_price();
				}else{
					//remove ticket from bookings and set for deletion if this is saved
					unset($this->tickets_bookings[$ticket_booking->ticket_id]);
					$this->tickets_bookings_deleted[$ticket_booking->ticket_id] = $ticket_booking;
				}
				return apply_filters('em_tickets_bookings_add', true, $this, $ticket_booking);
			}elseif( $ticket_booking->get_spaces() > 0 ){
				//new ticket in booking
				$this->tickets_bookings[$ticket_booking->ticket_id] = $ticket_booking;
				$this->get_spaces(true);
				$this->get_price();
				return apply_filters('em_tickets_bookings_add', true, $this, $ticket_booking);
			}
		} else {
			$this->add_error(get_option('dbem_booking_feedback_full'));
			return apply_filters('em_tickets_bookings_add', false, $this, $ticket_booking);
		}
		return apply_filters('em_tickets_bookings_add', false, $this, $ticket_booking);
	}
	
	/**
	 * Checks if this set has a specific ticket booked, returning the key of the ticket in the TicketsBookings->ticket_bookings array
	 * @param int $ticket_id
	 * @return mixed
	 */
	function has_ticket( $ticket_id ){
		foreach ($this->tickets_bookings as $key => $ticket_booking){
			if( $ticket_booking->ticket_id == $ticket_id ){
				return apply_filters('em_tickets_has_ticket',$key,$this);
			}
		}
		return apply_filters('em_tickets_has_ticket',false,$this);
	}
	
	/**
	 * Smart event locator, saves a database read if possible. 
	 */
	function get_booking(){

		$booking_id = $this->get_booking_id();
		if( is_object($this->booking) && get_class($this->booking)=='Booking' && $this->booking->booking_id == $booking_id ){
			return $this->booking;
		}
		
		if(is_numeric($booking_id)){
			$this->booking = Booking::get_by_id($booking_id);
		}

		$this->booking = new Booking;
		
		return apply_filters('em_tickets_bookings_get_booking', $this->booking, $this);;
	}
	
	function get_booking_id(){
		if( !empty($this->booking_id) || count($this->tickets_bookings) > 0 ) return $this->booking_id;
		
		$ticket_booking = $this->tickets_bookings[0];
		$this->booking_id = $ticket_booking->get_booking()->booking_id;
		return $this->booking_id;
	}
	
	/**
	 * Delete all ticket bookings
	 * @return boolean
	 */
	public function delete(): bool {
		global $wpdb;
	
		// Sicherheitscheck: Nur wer Events verwalten darf, darf löschen
		if (!current_user_can('delets_posts')) {
			$this->errors[] = __('You do not have permission to delete ticket bookings.', 'events');
			return false;
		}
	
		// Alle ticket_bookings zu dieser Buchung löschen
		$booking_id = (int) $this->get_booking_id();
		$result = $wpdb->delete(EM_TICKETS_BOOKINGS_TABLE, ['booking_id' => $booking_id]);
	
		return (bool) $result;
	}
	
	/**
	 * Go through the tickets in this object and validate them 
	 */
	function validate(){
		$errors = array();
		foreach($this->tickets_bookings as $ticket_booking){
			$errors[] = $ticket_booking->validate();
		}
		return apply_filters('em_tickets_bookings_validate', !in_array(false, $errors), $this);
	}
	
	/**
	 * Get the total number of spaces booked in this booking. Seting $force_reset to true will recheck spaces, even if previously done so.
	 * @param unknown_type $force_refresh
	 * @return mixed
	 */
	function get_spaces( $force_refresh=false ){
		if($force_refresh || $this->spaces == 0){
			$spaces = 0;
			foreach($this->tickets_bookings as $ticket_booking){
			    
				$spaces += $ticket_booking->get_spaces();
			}
			$this->spaces = $spaces;
		}
		return apply_filters('em_booking_get_spaces',$this->spaces,$this);
	}
	
	/**
	 * Gets the total price for this whole booking by adding up subtotals of booked tickets. Seting $force_reset to true will recheck spaces, even if previously done so.
	 * @param boolean $format
	 * @return float
	 */
	function get_price(){
		if( $this->price == 0 ){
			$price = 0;
			foreach($this->tickets_bookings as $ticket_booking){
				$price += $ticket_booking->get_price();
			}
			$this->price = apply_filters('em_tickets_bookings_get_price', $price, $this);
		}

		return $this->price;
	}
	
	/**
	 * Goes through each ticket and populates it with the bookings made
	 */
	function get_ticket_bookings(){
		foreach( $this->tickets as $ticket ){
			$ticket->get_bookings();
		}
	}	
	
	public static function build_sql_conditions(array $args): array {
		$conditions = [];
	
		// Nur ticket_status berücksichtigen
		if (isset($args['status']) && is_numeric($args['status'])) {
			$conditions['status'] = 'ticket_status = ' . (int) $args['status'];
		}
	
		return apply_filters('em_tickets_bookings_build_sql_conditions', $conditions, $args);
	}
	
	
	/* Overrides EM_Object method to apply a filter to result
	 * @see wp-content/plugins/events/classes/EM_Object#build_sql_orderby()
	 */
	public static function build_sql_orderby( $args, $accepted_fields, $default_order = 'ASC' ){
		return apply_filters( 'em_tickets_bookings_build_sql_orderby', SQLHelper::build_sql_orderby($args, $accepted_fields, get_option('dbem_events_default_order')), $args, $accepted_fields, $default_order );
	}
	
	/* 
	 * Adds custom Events search defaults
	 * @param array $array_or_defaults may be the array to override defaults
	 * @param array $array
	 * @return array
	 * @uses EM_Object#get_default_search()
	 */
	public static function get_default_search($input = []) {
		$defaults = [
			'status' => false,
			'person' => true,
			'limit' => 10,
			'page' => 1,
			'offset' => 0,
			'orderby' => ['event_start'],
			'order' => 'ASC',
		];
	
		$search = array_merge($defaults, $input);
	
		if (!current_user_can('manage_others_bookings')) {
			$search['owner'] = get_current_user_id();
		}
	
		// Clean up pagination logic
		if ($search['page'] > 1 && $search['limit'] > 0) {
			$search['offset'] = $search['limit'] * ($search['page'] - 1);
		}
	
		return $search;
	}

	//Iterator Implementation

	#[\ReturnTypeWillChange]
    public function rewind(){
        reset($this->tickets_bookings);
    }
	
	/**
	 * @return TicketBooking
	 */

	 #[\ReturnTypeWillChange]
    public function current(){
        $var = current($this->tickets_bookings);
        return $var;
    }
	/**
	 * @return int Ticket ID
	 */

	 #[\ReturnTypeWillChange]
    public function key(){
        $var = key($this->tickets_bookings);
        return $var;
    }
	/**
	 * @return Ticket_Booking
	 */

	 #[\ReturnTypeWillChange]
	public function next(){
        $var = next($this->tickets_bookings);
        return $var;
    }

	#[\ReturnTypeWillChange]
	public function valid(){
        $key = key($this->tickets_bookings);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }
    //Countable Implementation
    public function count() : int {
		return count($this->tickets_bookings);
    }
}
?>