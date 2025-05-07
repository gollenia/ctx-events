<?php

namespace Contexis\Events\Models;

use Contexis\Events\Collections\BookingCollection;
use Contexis\Events\Forms\AttendeesForm;
use Contexis\Events\Models\Event;
use DateTime;

/*
 * Handles a single ticket for an event or a booking
 * @author Thomas Gollenia
 *
 */
class Ticket {
	public int $ticket_id = 0;
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
   
	//Other Vars
	/**
	 * Contains only bookings belonging to this ticket. 
	 */
	public $bookings;
	public array $required_fields = array('ticket_name');
	protected $start;
	protected $end;

	/**
	 * An associative array containing event IDs as the keys and pending spaces as values.
	 * This is in array form for future-proofing since at one point tickets could be used for multiple events.
	 * @var array
	 */
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
		$event_available_spaces = $this->get_event()->get_available_spaces();
		$ticket_available_spaces = $this->ticket_spaces - $this->get_booked_spaces();
		if( get_option('dbem_bookings_approval_reserved')){
		    $ticket_available_spaces = $ticket_available_spaces - $this->get_pending_spaces();
		}
		$return = ($ticket_available_spaces <= $event_available_spaces) ? $ticket_available_spaces:$event_available_spaces;
		return apply_filters('em_ticket_get_available_spaces', $return, $this);
	}

	function get_spaces_by_status(int $status) {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT booking_meta FROM " . EM_BOOKINGS_TABLE . " 
			WHERE event_id = %d AND booking_status = %d",
			$this->event_id, $status
		);

		$results = $wpdb->get_col($sql);
		$spaces = 0;

		foreach ($results as $metadata) {
			$meta = json_decode($metadata, true) ?: [];
			if (!isset($meta['attendees'][$this->ticket_id])) {
				continue;
			}
			$spaces += count($meta['attendees'][$this->ticket_id]);
		}

		return $spaces;
	}
	
	function get_pending_spaces(): int {
		return $this->get_spaces_by_status(Booking::PENDING);
	}

	function get_booked_spaces(): int {
		return $this->get_spaces_by_status(Booking::APPROVED);
	}

	function get_event() : Event {
		return Event::get_by_id($this->event_id);
	}
	
	function get_bookings(): BookingCollection {
		global $wpdb;
	
		$status_cond = get_option('dbem_bookings_approval') ? 'booking_status = 1' : 'booking_status IN (0,1)';
		$sql = $wpdb->prepare("
			SELECT booking_id, booking_meta
			FROM {$wpdb->prefix}em_bookings
			WHERE event_id = %d AND $status_cond
		", $this->event_id);
	
		$rows = $wpdb->get_results($sql);
		$matching_bookings = [];
	
		foreach ($rows as $row) {
			$meta = json_decode($row->booking_meta, true) ?: [];
			if (!empty($meta['attendees'][$this->ticket_id])) {
				$matching_bookings[] = Booking::get_by_id((int)$row->booking_id);
			}
		}
	
		return BookingCollection::from_bookings($matching_bookings);
	}
	
	public function get_rest_fields() {

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
