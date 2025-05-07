<?php

namespace Contexis\Events\Collections;

use Contexis\Events\Models\Booking;
use \Contexis\Events\Models\Event;
use Contexis\Events\Models\Ticket;

/**
 * Handles multiple tickets for an event or a booking
 * @author Thomas Gollenia
 *
 */
class TicketCollection implements \IteratorAggregate, \Countable {
	
	private array $items = [];
	public int $event_id = 0;
	public ?Booking $booking = null;

	public static function find_by_event_id(int $event_id) : self {
		$ticket_meta = get_post_meta($event_id, '_event_tickets', true);
		if(empty($ticket_meta)) return new self();
		$instance = new self();
		$instance->event_id = $event_id;
		$ticket_ids = array_column($ticket_meta, 'ticket_id');
		$instance->load_tickets($ticket_ids);
		
		return $instance;
	}

	public static function find_by_booking(Booking $booking) : self {
		$used_ticket_ids = array_keys( $booking->booking_meta['attendees'] ?? [] );
		$instance = new self();
		$instance->event_id = $booking->event_id;
		$instance->booking = $booking;
		$instance->load_tickets($used_ticket_ids);
	
		return $instance;
	}

	public static function find_by_booking_id(int $booking_id) : self {
		$booking = Booking::get_by_id($booking_id);
		return self::find_by_booking($booking);
	}

	private function load_tickets(array $tickets_array) : void {
		foreach ($tickets_array as $ticket_id){
			$ticket = Ticket::get_by_id($this->event_id, $ticket_id);
			if(!$ticket) continue;
			$this->items[] = $ticket;
		}
	}

	public function get_available() : self {
		$available = array_filter($this->items, fn($ticket) => $ticket->is_available());
		$instance = new self();
		$instance->items = $available;
		$instance->event_id = $this->event_id;
		$instance->booking = $this->booking ?? null;
		return $instance;
	}
	
	function get_event() : Event {
		return Event::get_by_id($this->event_id);
	}

	public function get_rest_fields() : array {
		$data = [];
		foreach($this->items as $ticket) {
			$data[$ticket->ticket_id] = $ticket->get_rest_fields();
		}
		return $data;
	}

	public function getIterator() : \ArrayIterator {
		return new \ArrayIterator($this->items);
	}

    public function count() : int {
    	return count($this->items);
    }
}
