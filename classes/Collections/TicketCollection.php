<?php

namespace Contexis\Events\Collections;

use Contexis\Events\Model\Booking;
use \Contexis\Events\Models\Event;
use Contexis\Events\Models\Ticket;

/**
 * Deals with the ticket info for an event
 * @author marcus
 *
 */
class TicketCollection extends \EM_Object implements \Iterator, \Countable {
	

	public array $tickets = [];
	public int $index = 0;
	public int $event_id = 0;
	public Booking $booking;
	private int $spaces = 0;
	

	private function get_ticket_sorting() {
		$orderby_option = get_option('dbem_bookings_tickets_orderby');
		$order_by = get_option('dbem_bookings_tickets_ordering') ? array('ticket_order ASC') : array();
		$ticket_orderby_options = apply_filters('em_tickets_orderby_options', array(
			'ticket_price DESC, ticket_name ASC'=>__('Ticket Price (Descending)','events'),
			'ticket_price ASC, ticket_name ASC'=>__('Ticket Price (Ascending)','events'),
			'ticket_name ASC, ticket_price DESC'=>__('Ticket Name (Ascending)','events'),
			'ticket_name DESC, ticket_price DESC'=>__('Ticket Name (Descending)','events')
		));

		if( array_key_exists($orderby_option, $ticket_orderby_options) ) {
			$order_by[] = $orderby_option;
			return $order_by;
		}
		
		$order_by[] = 'ticket_price DESC, ticket_name ASC';
		return $order_by;
	}

	public static function find_by_event_id(int $event_id) {
		global $wpdb;
		$instance = new self();
		if( $event_id == 0 ) return $instance;
		$instance->event_id = $event_id;
		$order_by = $instance->get_ticket_sorting();
		$sql = "SELECT * FROM ". EM_TICKETS_TABLE ." WHERE event_id ='{$instance->event_id}' ORDER BY ".implode(',', $order_by);
		$tickets = $wpdb->get_results($sql, ARRAY_A);
		$instance->tickets = $instance->prepare_tickets($tickets);
		return $instance;
	}

	public static function find_by_booking(Booking $booking) {
		global $wpdb;
		$instance = new self();
		$instance->booking = $booking->booking;
		$instance->event_id = $booking->event_id;
		$order_by = $instance->get_ticket_sorting();
		$sql = "SELECT * FROM ". EM_TICKETS_TABLE ." WHERE ticket_id IN (SELECT ticket_id FROM ".EM_TICKETS_BOOKINGS_TABLE." WHERE booking_id='{$instance->booking->booking_id}') ORDER BY ".implode(',', $order_by);
		$tickets = $wpdb->get_results($sql, ARRAY_A);
		$instance->tickets = $instance->prepare_tickets($tickets);
		return $instance;
	}

	public static function find_by_booking_id(int $booking_id) {
		$booking = Booking::get_by_id($booking_id);
		return self::find_by_booking($booking);
	}

	public static function find_by_ids(array $ticket_ids = []) {
		$instance = new self();
		if (empty($ticket_ids)) return $instance; 
		if( is_object(current($ticket_ids)) && get_class(current($ticket_ids)) == 'Ticket' ){
			foreach($ticket_ids as $ticket){
				$instance->tickets[$ticket->ticket_id] = $ticket;
			}

			return $instance;
		}
		
		foreach($ticket_ids as $ticket){
			$ticket = new Ticket($ticket);
			$ticket->event_id = $instance->event_id;
			$instance->tickets[$ticket->ticket_id] = $ticket;				
		}

		return $instance;
		
	}

	private function prepare_tickets(array $tickets_array) {
		$tickets = [];
		foreach ($tickets_array as $ticket){
			$ticket = new Ticket($ticket);
			$ticket->event_id = $this->event_id;
			$tickets[$ticket->ticket_id] = $ticket;
		}
		return $tickets;
	}
	
	/**
	 * @return Event
	 */
	function get_event() : Event {
		return Event::find_by_id($this->event_id);
	}

	/**
	 * does this ticket exist?
	 * @return bool 
	 */
	function has_ticket($ticket_id){
		foreach( $this->tickets as $ticket){
			if($ticket->ticket_id == $ticket_id){
				return apply_filters('em_tickets_has_ticket',true, $ticket, $this);
			}
		}
		return apply_filters('em_tickets_has_ticket',false, false,$this);
	}

	public function get_rest_fields() {
		$data = [];
		foreach($this->tickets as $ticket) {
			$data[$ticket->ticket_id] = $ticket->get_rest_fields();
		}
		return $data;
	}
	
	/**
	 * Get the first Ticket object in this instance. Returns false if no tickets available.
	 * @return Ticket
	 */
	function get_first(){
		if( count($this->tickets) > 0 ){
			foreach($this->tickets as $ticket){
				return $ticket;
			}
		}else{
			return false;
		}
	}
	
	/**
	 * Delete tickets in this object
	 * @return boolean
	 */
	function delete(){
		global $wpdb;
		//get all the ticket ids
		$result = false;
		$ticket_ids = array();
		if( !empty($this->tickets) ){
			//get ticket ids if tickets are already preloaded into the object
			foreach( $this->tickets as $ticket ){
				$ticket_ids[] = $ticket->ticket_id;
			}
			//check that tickets don't have bookings
			if(count($ticket_ids) > 0){
				$bookings = $wpdb->get_var("SELECT COUNT(*) FROM ". EM_TICKETS_BOOKINGS_TABLE." WHERE ticket_id IN (".implode(',',$ticket_ids).")");
				if( $bookings > 0 ){
					$result = false;
					$this->add_error(__('You cannot delete tickets if there are any bookings associated with them. Please delete these bookings first.','events'));
				}else{
					$result = $wpdb->query("DELETE FROM ".EM_TICKETS_TABLE." WHERE ticket_id IN (".implode(',',$ticket_ids).")");
				}
			}
		}elseif( !empty($this->event_id) ){
			//if tickets aren't preloaded into object and this belongs to an event, delete via the event ID without loading any tickets
			$event_id = absint($this->event_id);
			$bookings = $wpdb->get_var("SELECT COUNT(*) FROM ". EM_TICKETS_BOOKINGS_TABLE." WHERE ticket_id IN (SELECT ticket_id FROM ".EM_TICKETS_TABLE." WHERE event_id='$event_id')");
			$ticket_ids = $wpdb->get_col("SELECT ticket_id FROM ". EM_TICKETS_TABLE." WHERE event_id='$event_id'");
			if( $bookings > 0 ){
				$result = false;
				$this->add_error(__('You cannot delete tickets if there are any bookings associated with them. Please delete these bookings first.','events'));
			}else{
				$result = $wpdb->query("DELETE FROM ".EM_TICKETS_TABLE." WHERE event_id='$event_id'");
			}
		}
		return apply_filters('em_tickets_delete', ($result !== false), $ticket_ids, $this);
	}
	
	/**
	 * Retrieve multiple ticket info via POST
	 * @return boolean
	 * @todo This function is not used anywhere in the plugin. It should be removed.
	 */
	/*
	function get_post(){
		//Build Event Array
		do_action('em_tickets_get_post_pre', $this);
		$current_tickets = $this->tickets; //save previous tickets so things like ticket_meta doesn't get overwritten
		$this->tickets = array(); //clean current tickets out
		
		if( !empty($_POST['em_tickets']) && is_array($_POST['em_tickets']) ){
			//get all ticket data and create objects
			
			$order = 1;
			foreach($_POST['em_tickets'] as $row => $ticket_data){
			    if( $row > 0 ){
			    	if( !empty($ticket_data['ticket_id']) && !empty($current_tickets[$ticket_data['ticket_id']]) ){
			    		$ticket = $current_tickets[$ticket_data['ticket_id']];
			    	}else{
			    		$ticket = new Ticket();
			    	}
					$ticket_data['event_id'] = $this->event_id;
					$ticket->get_post($ticket_data);
					$ticket->ticket_order = $order;
					if( $ticket->ticket_id ){
						$this->tickets[$ticket->ticket_id] = $ticket;
					}else{
						$this->tickets[] = $ticket;
					}
				    $order++;
			    }
			}
		}else{
			//we create a blank standard ticket
			$ticket = new Ticket(array(
				'event_id' => $this->event_id,
				'ticket_name' => __('Standard','events')
			));
			$this->tickets[] = $ticket;
		}
		return apply_filters('em_tickets_get_post', count($this->errors) == 0, $this);
	}
	*/

	/**
	 * Go through the tickets in this object and validate them 
	 */
	function validate(){
		$this->errors = array();
		foreach($this->tickets as $ticket){
			if( !$ticket->validate() ){
				$this->add_error($ticket->get_errors());
			} 
		}
		return apply_filters('em_tickets_validate', count($this->errors) == 0, $this);
	}
	
	/**
	 * Save tickets into DB 
	 */
	function save(){
		$result = true;
		foreach( $this->tickets as $ticket ){
			/* @var $ticket Ticket */
			$ticket->event_id = $this->event_id; //pass on saved event_data
			if( !$ticket->save() ){
				$result = false;
				$this->add_error($ticket->get_errors());
			}
		}
		return apply_filters('em_tickets_save', $result, $this);
	}
	
	/**
	 * Goes through each ticket and populates it with the bookings made
	 */
	function get_ticket_bookings(){
		foreach( $this->tickets as $ticket ){
			$ticket->get_bookings();
		}
	}

	
	
	/**
	 * Get the total number of spaces this event has. This will show the lower value of event global spaces limit or total ticket spaces. Setting $force_refresh to true will recheck spaces, even if previously done so.
	 * @param boolean $force_refresh
	 * @return int
	 */
	function get_spaces( $force_refresh=false ){
		$spaces = 0;
		if($force_refresh || $this->spaces == 0){
			foreach( $this->tickets as $ticket ){
				/* @var $ticket Ticket */
				$spaces += $ticket->get_spaces();
			}
			$this->spaces = $spaces;
		}
		return apply_filters('em_booking_get_spaces',$this->spaces,$this);
	}
	
	/**
	 * Returns the columns used in ticket public pricing tables/forms
	 * @param unknown_type $event
	 */
	function get_ticket_columns($event = false){
		if( !$event ) $event = $this->get_event();
		$columns = array( 'type' => __('Ticket Type','events'), 'price' => __('Price','events'), 'spaces' => __('Spaces','events'));
		if( $event->is_free() ) unset($columns['price']); //add event price
		return apply_filters('em_booking_form_tickets_cols', $columns, $event );
	}
	
	//Iterator Implementation
    public function rewind() : void {
        reset($this->tickets);
    }
	/**
	 * @return Ticket
	 */
    public function current() : Ticket {
        $var = current($this->tickets);
        return $var;
    }  

	#[\ReturnTypeWillChange]
    public function key(){
        $var = key($this->tickets);
        return $var;
    }
	/**
	 * @return Ticket
	 */
    public function next() : void {
        next($this->tickets);
    }  
    public function valid() : bool {
        $key = key($this->tickets);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }
    //Countable Implementation
    public function count() : int {
    	return count($this->tickets);
    }
}
