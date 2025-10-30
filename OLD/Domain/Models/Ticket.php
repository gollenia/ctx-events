<?php

namespace Contexis\Events\Domain\Models;

use Contexis\Events\Collections\BookingCollection;
use Contexis\Events\Forms\AttendeesForm;
use Contexis\Events\Models\Event;
use DateTime;
use Contexis\Events\Models\BookingStatus;

/*
 * Handles a single ticket for an event or a booking
 * @author Thomas Gollenia
 *
 */
class Ticket implements \JsonSerializable {
	public string $ticket_id = '';
	public int $event_id = 0;
	public string $ticket_name = '';
	public string $ticket_description = '';
	public float $ticket_price;
	protected ?DateTime $ticket_start = null;
	protected ?DateTime $ticket_end = null;
	public int $ticket_min = 0;
	public int $ticket_max = 0;
	public int $ticket_spaces = 0;
	public int $ticket_order = 0;
	public int $ticket_form = 0;
	public bool $ticket_enabled = true;

	protected array $pending_spaces = array();
	protected array $booked_spaces = array();
	protected array $bookings_count = array();
	
	public static function get_by_id($event_id, $ticket_id) : ?Ticket {
		$instance = new self();
		if(empty($event_id) || empty($ticket_id)) return null;
		$instance->event_id = $event_id;
		$tickets = get_post_meta($instance->event_id, '_event_tickets', true) ?: [];
		$ticket = array_filter($tickets, function($ticket) use ($ticket_id) {
			return $ticket['ticket_id'] == $ticket_id;
		});
		if(empty($ticket)) return $instance;
		$ticket = array_shift($ticket);
		$instance->ticket_id = $ticket['ticket_id'];
		$instance->ticket_name = $ticket['ticket_name'];
		$instance->ticket_description = $ticket['ticket_description'];
		$instance->ticket_price = $ticket['ticket_price'];
		$instance->ticket_start = $ticket['ticket_start'] ? new DateTime($ticket['ticket_start']) : null;
		$instance->ticket_end = $ticket['ticket_end'] ? new DateTime($ticket['ticket_end']) : null;
		$instance->ticket_min = $ticket['ticket_min'] ?: 0;
		$instance->ticket_max = $ticket['ticket_max'] ?: 0;
		$instance->ticket_spaces = $ticket['ticket_spaces'] ?: 0;
		$instance->ticket_order = $ticket['ticket_order'] ?: 0;
		$instance->ticket_form = $ticket['ticket_form'] ?: 0;
		$instance->ticket_enabled = $ticket['ticket_enabled'] == 1;
		return $instance;
	}

	public static function from_array(int $event_id, array $data) : self {
		$instance = new self();
		$instance->event_id = $event_id;
		$instance->ticket_id = $data['ticket_id'] ?? 0;
		$instance->ticket_name = $data['ticket_name'] ?? '';
		$instance->ticket_description = $data['ticket_description'] ?? '';
		$instance->ticket_price = $data['ticket_price'] ?? 0.0;
		$instance->ticket_start = !empty($data['ticket_start']) ? new DateTime($data['ticket_start']) : null;
		$instance->ticket_end = !empty($data['ticket_end']) ? new DateTime($data['ticket_end']) : null;
		$instance->ticket_min = $data['ticket_min'] ?? 0;
		$instance->ticket_max = $data['ticket_max'] ?? 0;
		$instance->ticket_spaces = $data['ticket_spaces'] ?? 0;
		$instance->ticket_order = $data['ticket_order'] ?? 0;
		$instance->ticket_form = $data['ticket_form'] ?? 0;
		$instance->ticket_enabled = !empty($data['ticket_enabled']) && $data['ticket_enabled'] == 1;
		return $instance;
	}
	
	function is_available() : bool
	{
		if(!$this->ticket_enabled) return false;

		$now = time();
		if (!empty($this->ticket_start) && $this->ticket_start->getTimestamp() > $now) return false;
		if (!empty($this->ticket_end) && $this->ticket_end->getTimestamp() < $now) return false;

		$event = Event::get_by_id($this->event_id);

		if ($event->get_rsvp_end()->getTimestamp() < $now) return false;
		if ($event->get_rsvp_start()->getTimestamp() > $now) return false;

		$available_spaces = $this->get_available_spaces();

		if ($available_spaces <= 0) return false;
		if (!empty($this->ticket_min) && $available_spaces < $this->ticket_min) return false;
		
		return true;
	}

	function get_available_spaces() : int {
		$event_available_spaces = $this->get_event()->spaces->available();
		$ticket_available_spaces = $this->ticket_spaces - $this->get_booked_spaces();
		if( get_option('dbem_bookings_approval_reserved')){
		    $ticket_available_spaces = $ticket_available_spaces - $this->get_pending_spaces();
		}
		$return = ($ticket_available_spaces <= $event_available_spaces) ? $ticket_available_spaces : $event_available_spaces;
		return apply_filters('em_ticket_get_available_spaces', $return, $this);
	}

	function get_spaces_by_status(BookingStatus $status) {
		$spaces = 0;
		$bookings = BookingCollection::find([
			'event_id' => $this->event_id,
			'status' => $status
		]);
		
		foreach ($bookings as $booking) {
			if (!isset($booking->attendees[$this->ticket_id])) continue;
			$spaces += count($booking->attendees[$this->ticket_id]);
		}

		return $spaces;
	}
	
	function get_pending_spaces(): int {
		return $this->get_spaces_by_status(BookingStatus::PENDING);
	}

	function get_booked_spaces(): int {
		return $this->get_spaces_by_status(BookingStatus::APPROVED);
	}

	function get_event() : Event {
		return Event::get_by_id($this->event_id);
	}
	
	function get_bookings(): BookingCollection {
		$status_cond = get_option('dbem_bookings_approval') ? [BookingStatus::APPROVED] : [BookingStatus::APPROVED, BookingStatus::PENDING];
		$bookings = BookingCollection::find([
			'event_id' => $this->event_id,
			'status' => $status_cond
		]);
	
		foreach ($bookings as $booking) {
			if (empty($booking->attendees[$this->ticket_id])) {
				$bookings->remove($booking);
			}
		}
	
		return $bookings;
	}
	
	public function jsonSerialize() : array {

		$fields = [];
		$raw_fields = AttendeesForm::get_attendee_form($this->event_id);
		foreach($raw_fields as $field) {
			$fields[$field['fieldid']] = '';
		}

		return [
			
                "id" => $this->ticket_id,
                "event_id" => $this->event_id,
                "is_available" => $this->is_available(),
                "max" => intval($this->ticket_max ? min( $this->ticket_spaces, $this->ticket_max ) : $this->ticket_spaces),
                "price" => floatval($this->ticket_price),
                "min" => $this->ticket_min ?: 0,
                "name" => $this->ticket_name,
                "description" => $this->ticket_description,
				"fields" => $fields
		];
	}
}
