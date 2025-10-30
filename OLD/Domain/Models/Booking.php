<?php

namespace Contexis\Events\Models;

use Contexis\Events\Collections\RecordCollection;
use Contexis\Events\Collections\TicketCollection;
use Contexis\Events\Collections\TransactionCollection;
use Contexis\Events\Intl\Price;
use Contexis\Events\Core\Contracts\Application;
use Contexis\Events\Emails\Mailer;
use Contexis\Events\Models\Event;
use Contexis\Events\Payment\GatewayCollection;
use Contexis\Events\PostTypes\EventPost;
use Contexis\Events\Views\BookingView;
use DateTime;
use WP_REST_Request;
use JsonSerializable;
use Contexis\Events\Repositories\BookingRepository;
use Contexis\Events\Models\BookingStatus;
use Contexis\Events\Payment\Gateway;

class Booking implements JsonSerializable {

	use Application;

	public int $id = 0;
	public int $event_id = 0;
	public int $spaces = 0;
	public string $user_email = '';
	public ?DateTime $date = null;
	public BookingStatus $status = BookingStatus::PENDING;
	public float $full_price = 0.0;
	public float $donation = 0.0;
	public array $registration = []; 
	public array $attendees = [];
	public string $gateway = '';
	public ?Coupon $coupon = null;
	public ?TransactionCollection $transactions = null;
	public ?RecordCollection $notes = null;
	public ?RecordCollection $log = null;

	public array $errors = [];

	public static function get_status_label( BookingStatus $status ) : string 
	{
		return $status->label();
	}

	public static function get_status_array(): array {
		return array_column(
			array_map(
				fn($status) => ['key' => $status->value, 'label' => $status->label()],
				BookingStatus::cases()
			),
			'label',
			'key'
		);
	}

	public function get_first_name() : string 
	{
		return $this->registration['first_name'] ?? "";
	}

	public function get_last_name() : string 
	{
		return $this->registration['last_name'] ?? "";
	}

	public function get_full_name() : string 
	{
		return $this->get_first_name() . ' ' . $this->get_last_name();
	}
	
	public function get_attendees() : array 
	{
		$result = [];
		foreach($this->attendees as $ticket_id => $attendees){
			foreach($attendees as $attendee) {
				array_push($result, ["ticket_id" => $ticket_id, "fields" => $attendee]);
			}
		}

		return $result;
	}

	public function date() : DateTime
	{
		if ($this->date instanceof \DateTime) {
			return $this->date;
		}
		
		return new DateTime("1st January 1970");
	}
	
	function save() : bool 
	{
		if( $this->id ) {
			return BookingRepository::update($this);
		}
		$result = BookingRepository::create($this);
		
		if( $result === false ) {
			$this->errors[] = __('Booking could not be saved', 'events');
			return false;
		}
		$this->id = $result;
		if( !$this->coupon ) return true;
		$this->coupon->increment_used();
		return true;
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

	function get_booking_url() : string 
	{
		if( $this->id == 0 ) return $this->get_admin_url();
		return add_query_arg(['booking_id'=>$this->id, 'em_ajax'=>null, 'em_obj'=>null], $this->get_admin_url());
	}

	private function validate_ticket_availability() : bool {
		$attendees = $this->metadata['attendees'] ?? [];
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
	
	public function validate() : bool {

		$result = true;

		if( !$this->event_id || !($this->get_event() instanceof Event) || $this->get_event()->event_id == 0 ){
			$this->errors[] = __('Event does not exist.', 'events');
			$result = false;
		}

		if( !$this->validate_ticket_availability() ) {
			$this->errors[] = __('One or more tickets are not available.', 'events');
			$result = false;
		}

		if( $this->get_event()->spaces->available() < $this->get_booked_spaces() ){
			$this->errors[] = get_option('dbem_booking_feedback_full');
			$result = false;
		}
		
		if( !empty($this->errors) ){
			$result = false;
		}
		return apply_filters('em_booking_validate',$result, $this);
	}

	function get_booked_spaces( bool $force_refresh = false ) : int {
		if ( $this->spaces == 0 || $force_refresh ) {
			$spaces = 0;
	
			if ( !empty($this->attendees) && is_array($this->attendees) ) {
				foreach ( $this->attendees as $attendee_group ) {
					if ( is_array($attendee_group) ) {
						$spaces += count($attendee_group);
					}
				}
			}
	
			$this->spaces = $spaces;
		}
	
		return apply_filters('em_booking_get_spaces', $this->spaces, $this);
	}
	
	

	function get_price() : float 
	{
		$price = $this->get_price_base();
		$price -= $this->get_coupon_discount();
		$price += $this->donation;
		return round($price, 2);
	}
	
	public function get_price_base(): float {
		$total = 0.0;
		$tickets = TicketCollection::find_by_booking($this);
		foreach ( $this->attendees as $attendee ) {
			$ticket = $tickets->get_ticket_by_id( $attendee['ticket_id'] );
			if ( $ticket ) {
				$total += floatval( $ticket->ticket_price );
			}
		}
		return $total;
	}

	public function get_coupon_discount() : float 
	{
		if( !$this->coupon ) return 0.0;
		$coupon = $this->coupon;
		if( !$coupon->validate($this->get_event()) ) return 0.0;
		$discount = $coupon->get_discount($this->get_price_base());
		return apply_filters('em_booking_get_coupon_discount', $discount, $this, $coupon);
	}

	
	function get_price_summary_array(){
	    $summary = array();
	    $summary['total_base'] = $this->get_price_base();
	    $summary['discounts'] = $this->get_coupon_discount();
	    $summary['donation'] = $this->donation;
	    $summary['total'] =  $this->get_price();
	    return $summary;
	}
	
	/**
	 * Returns the amount paid for this booking. By default, a booking is considered either paid in full or not at all depending on whether the booking is confirmed or not.
	 * @param boolean $format If set to true a currency-formatted string value is returned
	 * @return string|float
	 */
	function get_total_paid( ) : float {
		$status = ($this->status == 0 && !get_option('dbem_bookings_approval') ) ? 1:$this->status;
		$total = $status ? $this->get_price() : 0;
		$total = apply_filters('em_booking_get_total_paid', $total, $this);
		return floatval($total);
	}
	

	function get_event() : Event {
		if($this->event_id == 0) return new Event();
		return Event::get_by_id($this->event_id);
	}
	
	function get_tickets() : TicketCollection {
		return TicketCollection::find_by_booking($this);
	}

	
	function get_status() : string 
	{
		$status = ($this->status == BookingStatus::PENDING && !get_option('dbem_bookings_approval') ) ? BookingStatus::APPROVED : $this->status;
		return apply_filters('em_booking_get_status', self::get_status_label($status), $this);
	}
	
	function delete() : bool 
	{
		if(!current_user_can('delete_post')) {
			$this->errors[] = __('You don\'t have the necessary rights to delete bookings', 'events');
			return false;
		}

		if(!BookingRepository::delete($this)){
			$this->errors[] = sprintf(__('Booking could not be deleted', 'events'), __('Booking','events'));
			return false;
		}

		$this->status = BookingStatus::DELETED;
		return apply_filters('em_booking_delete', $this);
	}
	
	function cancel($email = true) : bool 
	{
		return $this->set_status(BookingStatus::CANCELED, $email);
	}
	
	function approve($email = true, $ignore_spaces = false) : bool 
	{
		return $this->set_status(BookingStatus::APPROVED, $email, $ignore_spaces);
	}	

	function reject($email = true) : bool 
	{
		return $this->set_status(BookingStatus::REJECTED, $email);
	}	
	
	function unapprove($email = true) : bool 
	{
		return $this->set_status(BookingStatus::PENDING, $email);
	}
	
	function set_status(BookingStatus $status, bool $send_mail = true, $ignore_spaces = false) : bool 
	{
		$action_string = strtolower(self::get_status_label($status)); 
		
		if(!$ignore_spaces && $status == BookingStatus::PENDING){
			if( !$this->is_reserved() && $this->get_event()->spaces->available() < $this->get_booked_spaces() && !get_option('dbem_bookings_approval_overbooking') ){
				$this->errors[] = sprintf(__('Not approved, spaces full.','events'), $action_string);
				return apply_filters('em_booking_set_status', false, $this);
			}
		}
		error_log(sprintf('Setting booking %d to status %d (%s)', $this->id, $status, $action_string));
		$previous_status = $this->status;
		$this->status = $status;
		$this->log->add(sprintf(__('Booking set to %s', 'events'), $this->get_full_name(), self::get_status_label($status)));
		if(!BookingRepository::update($this)){
			$this->errors[] = sprintf(__('Booking could not be %s.','events'), $action_string);
			return apply_filters('em_booking_set_status', false, $this);
		}

		$result = apply_filters('em_booking_set_status', true, $this); 

		if( !$result ){
			$this->errors[] = sprintf(__('Booking could not be %s.','events'), $action_string);
			return false;
		}

		if(!$send_mail || $previous_status == $this->status) return true;

		$this->errors[] = __('ERROR : Email Not Sent.','events');
		return true;
	}
	
	/**
	 * Returns true if booking is reserving a space at this event, whether confirmed or not 
	 */
	function is_reserved(){
	    $result = false;
	    if( $this->status == BookingStatus::PENDING && get_option('dbem_bookings_approval_reserved') ){
	        $result = true;
	    }elseif( $this->status == BookingStatus::PENDING && !get_option('dbem_bookings_approval') ){
	        $result = true;
	    }elseif( $this->status == BookingStatus::APPROVED ){
	        $result = true;
	    }
	    return apply_filters('em_booking_is_reserved', $result, $this);
	}
	
	function is_pending() : bool
	{
		$result = ($this->is_reserved() || $this->status == 0) && $this->status != 1;
	    return apply_filters('em_booking_is_pending', $result, $this);
	}

	function is_paid() : bool
	{
		$result = $this->get_total_paid() >= $this->get_price() && $this->get_price() > 0;
	    return apply_filters('em_booking_is_paid', $result, $this);
	}
	
	function get_admin_url() : string
	{
		return is_admin() ? EventPost::get_admin_url(). "&page=events-bookings&event_id=".$this->event_id."&booking_id=".$this->id : "";
	}

	static function booking_enabled() : array
	{
		$enabled = [
			'is_enabled' => true,
			'message' => ''
			
		];
		
		$active_gateways = GatewayCollection::active();

		if( count($active_gateways) == 0 ){
			$enabled['is_enabled'] = false;
			$enabled['message'] = __('No payment gateways are enabled. Please enable at least one payment gateway.', 'events');
			return $enabled;
		}

		if( $active_gateways->has('offline') && (!get_option('em_offline_iban', false) || !get_option('em_offline_beneficiary', false) || !get_option('em_offline_bank', false)) ) {
			$enabled['is_enabled'] = false;
			$missing_fields = array();
			if( !get_option('em_offline_iban', false) ) $missing_fields[] = __('IBAN', 'events');
			if( !get_option('em_offline_beneficiary', false) ) $missing_fields[] = __('Beneficiary', 'events');
			if( !get_option('em_offline_bank', false) ) $missing_fields[] = __('Bank', 'events');
			$enabled['message'] = __('Offline Payment is not configured correctly. The following fields are missing:', 'events') . ' ' . implode(', ', $missing_fields) . __('. Please check your gateway settings.', 'events');
			return $enabled;
		}

		if( $active_gateways->has('mollie') && !get_option('em_mollie_api_key', false) ) {
			$enabled['is_enabled'] = false;
			$enabled['message'] = __('Mollie API Key is not set. Please check your gateway settings.', 'events');
			return $enabled;
		}

		return $enabled;
	}

	function to_array() : array {
		return [
			'event_id' => $this->event_id,
			'spaces' => $this->spaces,
			'user_email' => $this->user_email,
			'date' => $this->date?->format('c'),
			'status' => $this->status,
			'full_price' => $this->full_price,
			'donation' => $this->donation,
			'registration' => json_encode($this->registration),
			'attendees' => json_encode($this->attendees),
			'coupon_id' => $this->coupon->id ?? 0,
			'gateway' => $this->gateway,
			'notes' => json_encode($this->notes ? $this->notes->jsonSerialize() : []),
			'log' => json_encode($this->log ? $this->log->jsonSerialize() : []),
			'transactions' => json_encode($this->transactions ? $this->transactions->to_array() : [])
		];
	}

	public function jsonSerialize(): mixed
	{
		return [
			'date' => $this->date?->format(DATE_ATOM),
			'full_name' => $this->get_full_name(),
			'user_email' => $this->user_email,
			'event' => $this->get_event()->jsonSerialize(),
			'id' => $this->id,
			'status' => $this->status,
			'status_array' => $this->get_status_array(),
			'price' => $this->get_price(),
			'donation' => $this->donation,
			'paid' => $this->get_price_summary_array(),
			'gateway' => $this->gateway,
			'coupon' => $this->coupon ? $this->coupon->jsonSerialize() : null,
			'transactions' => $this->transactions ? $this->transactions->jsonSerialize() : [],
			'notes' => $this->notes->jsonSerialize(),
			'log' => $this->log->jsonSerialize(),
			'attendees' => $this->attendees,
			'registration' => $this->registration,
		];
	}

}