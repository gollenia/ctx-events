<?php

namespace Contexis\Events\Collections;

use Contexis\Events\Models\Booking;
use Contexis\Events\Collections\TicketCollection;
use Contexis\Events\Models\Ticket;
use Contexis\Events\Models\Event;
use Contexis\Events\EM_Object;
use Contexis\Events\Repositories\BookingRepository;
use IteratorAggregate;
use Countable;

class BookingCollection implements IteratorAggregate, Countable, \JsonSerializable {
	
	public array $items = [];

	var $tickets;
	
	public int $event_id = 0;
	
	var $spaces;
	
	protected $booked_spaces;
	protected $pending_spaces;
	protected $available_spaces;


	public static function from_event(Event $event) : BookingCollection {
		$bookings = BookingCollection::find([
			'event' => $event->event_id
		]);

		$bookings->event_id = $event->event_id;
		return $bookings;
	}

	public static function from_bookings(array $bookings) : BookingCollection {
		$instance = new self();
		foreach( $bookings as $booking ){
			$instance->items[] = $booking;
		}
		return $instance;
	}

	public function remove(Booking $item): void {
        $this->items = array_filter(
            $this->items,
            fn($i) => $i !== $item
        );
    
        $this->items = array_values($this->items);
    }

	public function add(Booking $item): void {
		if (!in_array($item, $this->items, true)) {
			$this->items[] = $item;
		}
	}

	/**
	 * Creates a BookingCollection from an array of booking IDs
	 * @param array<int> $booking_ids
	 * @return BookingCollection
	 */
	public static function from_booking_ids(array $booking_ids) : BookingCollection {
		$instance = new self();
		foreach( $booking_ids as $booking_id ){
			$instance->items[] = Booking::get_by_id(absint($booking_id));
		}
		return $instance;
	}
	
	public function __isset( $prop ){
		if( $prop == 'items' ){
			return !empty($this->items);
		}
		return isset($this->$prop);
	}


	
	/**
	 * Smart event locator, saves a database read if possible. Note that if an event doesn't exist, a blank object will be created to prevent duplicates.
	 */
	public function get_event() : ?Event {
		if (!empty($this->event_id) && is_numeric($this->event_id)) {
			return Event::get_by_id($this->event_id);
		}
		
		if (!empty($this->items) && is_array($this->items)) {
			foreach ($this->items as $booking) {
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
		foreach( $tickets as $ticket){
			
			if($ticket->ticket_id == $ticket_id){
				return apply_filters('em_bookings_ticket_exists',true, $ticket, $this);
			}
		}
		return apply_filters('em_bookings_ticket_exists',false, false,$this);
	}
	
	function has_space( $include_member_tickets = false ){
		return count($this->get_available_tickets( $include_member_tickets )->tickets) > 0;
	}
	
	public static function find( $args = array() ) : BookingCollection {
		$bookings = BookingRepository::find($args);
		return $bookings;
	}
	
	public function getIterator(): \Traversable {
        return new \ArrayIterator($this->items);
    }

	public function count(): int {
        return count($this->items);
    }

	public function jsonSerialize(): mixed {
		return array_map(function($item) {
			return $item->jsonSerialize();
		}, $this->items);
	}
}
