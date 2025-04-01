<?php

namespace Contexis\Events\Tickets;

use Contexis\Events\Model\Booking;
use Contexis\Events\Models\Ticket;

class TicketBooking extends \EM_Object{
	//DB Fields
	public $ticket_booking_id;
	public int $booking_id = 0;
	public $ticket_id;
	public $ticket_booking_price;
	public $ticket_booking_spaces;
	public array $fields = array(
		'ticket_booking_id' => array('name'=>'id','type'=>'%d'),
		'ticket_id' => array('name'=>'ticket_id','type'=>'%d'),
		'booking_id' => array('name'=>'booking_id','type'=>'%d'),
		'ticket_booking_price' => array('name'=>'price','type'=>'%f'),
		'ticket_booking_spaces' => array('name'=>'spaces','type'=>'%d')
	);
	
	var $ticket;
	
	public Booking $booking;
	public array $required_fields = array( 'ticket_id', 'ticket_booking_spaces');
	
	function __construct( $ticket_data = false ){
		if( $ticket_data !== false ){
			//Load ticket data
			$ticket = array();
			if( is_array($ticket_data) ){
				$ticket = $ticket_data;
			}elseif( is_numeric($ticket_data) ){
				//Retreiving from the database		
				global $wpdb;
				$sql = "SELECT * FROM ". EM_TICKETS_BOOKINGS_TABLE ." WHERE ticket_booking_id ='$ticket_data'";   
			  	$ticket = $wpdb->get_row($sql, ARRAY_A);
			}
			//Save into the object
			$this->from_array($ticket);
			//$this->compat_keys();
		}
	}
	
	
	function __sleep(){
		return array( 'ticket_booking_id','booking_id','ticket_id','ticket_booking_price','ticket_booking_spaces' );
	}
	
	/**
	 * Saves the ticket into the database, whether a new or existing ticket
	 * @return boolean
	 */
	function save(){
		global $wpdb;
		$table = EM_TICKETS_BOOKINGS_TABLE;
		do_action('em_ticket_booking_save_pre',$this);
		//First the person
		if($this->validate()){			
			//Now we save the ticket
			$this->booking_id = $this->get_booking()->booking_id; //event wouldn't exist before save, so refresh id
			$data = $this->to_array(true); //add the true to remove the nulls
			$result = null;
			if($this->ticket_booking_id != ''){
				if($this->get_spaces() > 0){
					$where = array( 'ticket_booking_id' => $this->ticket_booking_id );  
					$result = $wpdb->update($table, $data, $where, $this->get_types($data));
					$this->feedback_message = __('Changes saved','events');
				}else{
					$this->result = $this->delete(); 
				}
			}else{
				if($this->get_spaces() > 0){
					//TODO better error handling
					$result = $wpdb->insert($table, $data, $this->get_types($data));
				    $this->ticket_booking_id = $wpdb->insert_id;  
					$this->feedback_message = __('Ticket booking created','events'); 
				}else{
					//no point saving a booking with no spaces
					$result = false;
				}
			}
			if( $result === false ){
				$this->feedback_message = __('There was a problem saving the ticket booking.', 'events');
				$this->errors[] = __('There was a problem saving the ticket booking.', 'events');
			}
			//$this->compat_keys();
			return apply_filters('em_ticket_booking_save', ( count($this->errors) == 0 ), $this);
		}else{
			$this->feedback_message = __('There was a problem saving the ticket booking.', 'events');
			$this->errors[] = __('There was a problem saving the ticket booking.', 'events');
			return apply_filters('em_ticket_booking_save', false, $this);
		}
		return true;
	}	
	

	/**
	 * Validates the ticket for saving. Should be run during any form submission or saving operation.
	 * @return boolean
	 */
	function validate(){
		if( $this->ticket_booking_spaces == 0 ){
			$this->errors[] = __('You must book at least one space.','events');
		}

		return apply_filters('em_ticket_booking_validate', count($this->errors) == 0, $this );
	}
	
	/**
	 * Get the total number of spaces booked for this ticket within this booking.
	 * @return int
	 */
	function get_spaces(){
		return apply_filters('em_booking_get_spaces',$this->ticket_booking_spaces,$this);
	}

	function get_price(){
		if( $this->ticket_booking_price == 0 ){
			$this->ticket_booking_price = $this->get_ticket()->get_price() * $this->ticket_booking_spaces;
			$this->ticket_booking_price = apply_filters('em_ticket_booking_get_price', $this->ticket_booking_price, $this);
		}
		return $this->ticket_booking_price;

	}


	function get_booking() : Booking {
		if( is_object($this->booking) && get_class($this->booking)=='Booking' && ($this->booking->booking_id == $this->booking_id || (empty($this->ticket_booking_id) && empty($this->booking_id))) ){
			return $this->booking;
		}

		$this->booking = Booking::get_by_id($this->booking_id);
		return $this->booking;
	}
	
	/**
	 * Gets the ticket object this booking belongs to, saves a reference in ticket property
	 * @return Ticket
	 */
	function get_ticket(){
		$ticket_id = key_exists('ticket_id', $_REQUEST) ? $_REQUEST['ticket_id'] : 0;
		$ticket = new Ticket($ticket_id);
		if( is_object($this->ticket) && get_class($this->ticket)=='Ticket' && $this->ticket->ticket_id == $this->ticket_id ){
			return $this->ticket;
		}elseif( is_object($ticket) && $ticket->ticket_id == $this->ticket_id ){
			$this->ticket = $ticket;
		}else{
			$this->ticket = new Ticket($this->ticket_id);
		}
		return apply_filters('em_ticket_booking_get_ticket', $this->ticket, $this);
	}
	
	/**
	 * I wonder what this does....
	 * @return boolean
	 */
	function delete(){
		global $wpdb;
		if( $this->ticket_booking_id ){
			$sql = $wpdb->prepare("DELETE FROM ". EM_TICKETS_BOOKINGS_TABLE . " WHERE ticket_booking_id=%d LIMIT 1", $this->ticket_booking_id);
		}elseif( !empty($this->ticket_id) && !empty($this->booking_id) ){
			//in the event a ticket_booking_id isn't available we can delete via the booking and ticket id
			$sql = $wpdb->prepare("DELETE FROM ". EM_TICKETS_BOOKINGS_TABLE . " WHERE ticket_id=%d AND booking_id=%d LIMIT 1", $this->ticket_id, $this->booking_id);
		}else{
			//cannot delete ticket
			$result = false;
		}
		if( !empty($sql) ){
			$result = $wpdb->query( $sql );
		}
		return apply_filters('em_ticket_booking_delete', ($result !== false ), $this);
	}
	
	
	
	
	/**
	 * Can the user manage this event? 
	 */
	function can_manage( $owner_capability = false, $admin_capability = false, $user_to_check = false ){
		return ( $this->get_booking()->can_manage() );
	}
}
?>