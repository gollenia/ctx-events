<?php

namespace Contexis\Events\Models;

use Contexis\Events\Collections\NoteCollection;
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
	public int $coupon_id = 0;
	public bool $consent = false;
	public ?TransactionCollection $transactions = null;
	
	public ?NoteCollection $notes = null;
	public ?NoteCollection $log = null;

	public array $errors = [];
	
	public int $mails_sent = 0;

	public BookingStatus $previous_status = BookingStatus::PENDING;

	private array $status_actions = ['unapprove' => 0, 'approve' => 1, 'reject' => 2, 'cancel' => 3];

	public static function get_by_id(int $booking_id) : ?Booking 
	{
		return BookingRepository::get_by_id($booking_id);
	}

	public static function from_array( array $array = array() ) : Booking 
	{	
		if(!is_array($array)) return new Booking();	
		$instance = new self();
		
		$instance->registration = json_decode($array['registration'], true);
		$instance->user_email = $instance->registration['user_email'] ?? "";
		$instance->attendees = json_decode($array['attendees'], true);
		$instance->date = new DateTime($array['date']);
		$instance->notes = new NoteCollection(json_decode($array['notes'], true));
		$instance->log = new NoteCollection(json_decode($array['log'], true));
		$instance->transactions = TransactionCollection::from_array(json_decode($array['transactions'], true) ?: []);
		
		foreach($array as $key => $value){
			if( in_array($key, ['registration', 'date', 'attendees']) ) continue;
			if( property_exists($instance, $key) ){
				$instance->$key = $value;
			}
		}
		
		$instance->previous_status = $instance->status;
		return $instance;
	}

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

	public static function from_rest_request(\WP_REST_Request $request) : self 
	{
		$instance = new self();
		$instance->log = new NoteCollection();
		$instance->notes = new NoteCollection();
		if( !$instance->apply_rest_data($request) ) {
			return $instance;
		}
		
		return $instance;
	}

	public function apply_rest_data(WP_REST_Request $request) : bool {
		$payload = $request->get_params();
		if(!$this->event_id) {
			$this->event_id = isset($payload['event_id']) ? absint($payload['event_id']) : 0;
		}
		$this->registration = $payload['registration'];
		$this->user_email = $this->registration['user_email'] ?? '';
		$this->attendees = $payload['attendees'] ?? [];
		$this->gateway = $payload['gateway'];
		if( !empty($payload['coupon']) ) {
			$this->coupon_id = Coupon::get_by_code($payload['coupon'])->id ?? 0;
		}

		$this->full_price = $this->get_price();

		if( isset($payload['donation']) && floatval($payload['donation']) > 0 ){
			$this->donation = floatval($payload['donation']);
		}
		
		$this->consent = !empty($payload['data_privacy_consent']) && $payload['data_privacy_consent'] === 'true';
		$this->date = new DateTime();
		$this->spaces = count($payload['attendees']);
		$this->get_status($payload);
		$this->notes = NoteCollection::from_array($payload['notes'] ?? []);

		return true;
	}

	public function apply_partial_rest_data(WP_REST_Request $request) {
		if ($request->has_param('status') && is_numeric($request['status'])) {
			$this->set_status($this->status_actions[$request['status']] ?? 0);
		}

		if ($request->has_param('note')) {
			$this->notes->add(
				$request['note'],
			);
			$this->save();
		}
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
			$this->log->add('Booking updated');
			return BookingRepository::update($this);
		}
		$this->log->add('Booking created');
		$result = BookingRepository::create($this);
		if( $result === false ) {
			$this->errors[] = __('Booking could not be saved', 'events');
			return false;
		}
		$this->id = $result;
		return true;
	}

	function to_array() : array {
		return [
			'event_id' => $this->event_id,
			'spaces' => $this->spaces,
			'user_email' => $this->user_email,
			'date' => current_time('mysql'),
			'status' => $this->status,
			'full_price' => $this->full_price,
			'donation' => $this->donation,
			'registration' => json_encode($this->registration),
			'attendees' => json_encode($this->attendees),
			'coupon_id' => $this->coupon_id,
			'consent' => $this->consent ? 1 : 0,
			'gateway' => $this->gateway,
			'notes' => json_encode($this->notes ? $this->notes->jsonSerialize() : []),
			'log' => json_encode($this->log ? $this->log->jsonSerialize() : []),
			'transactions' => json_encode($this->transactions ? $this->transactions->to_array() : []),
		];
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
		return $icons[$this->status->value];
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
	
	public static function validate_request(WP_REST_Request $request) : array
	{
		$result = [];

		$parameters = $request->get_params();
		if(!isset($parameters['event_id'])  || !is_numeric($parameters['event_id']) || $parameters['event_id'] <= 0){
			$result[] = __('You must select an event to book.', 'events');
		}

		if(!isset($parameters['registration']) || !is_array($parameters['registration']) || count($parameters['registration']) == 0){
			$result[] = __('You must provide registration details to book an event.', 'events');
		}

		if(!isset($parameters['attendees']) || !is_array($parameters['attendees']) || count($parameters['attendees']) == 0){
			$result[] = __('You must request at least one space to book an event.', 'events');
		}

		if(!isset($parameters['registration']['user_email']) || !is_email($parameters['registration']['user_email'])){
			$result[] = __('You must provide a valid email address to book an event.', 'events');
		}

		if(!isset($parameters['gateway']) || !is_string($parameters['gateway']) || empty($parameters['gateway'])){
			$result[] = __('You must select a payment gateway to book an event.', 'events');
		}

		if(!isset($parameters['registration']['first_name']) || !is_string($parameters['registration']['first_name']) || empty($parameters['registration']['first_name'])){
			$result[] = __('You must provide a first name to book an event.', 'events');
		}

		if(!isset($parameters['registration']['last_name']) || !is_string($parameters['registration']['last_name']) || empty($parameters['registration']['last_name'])){
			$result[] = __('You must provide a last name to book an event.', 'events');
		}

		return apply_filters('em_booking_validate_request', $result, $request);

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

	function get_payment_info() 
	{
		return GatewayCollection::active()->get($this->gateway)->get_payment_info($this);
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
		foreach ( $this->attendees as $ticket_id => $attendees ) {
			$ticket = \Contexis\Events\Models\Ticket::get_by_id( $this->event_id, $ticket_id );
			if ( $ticket ) {
				$total += count( $attendees ) * floatval( $ticket->ticket_price );
			}
		}
		return $total;
	}

	public function get_coupon_discount() : float 
	{
		if( empty($this->coupon_id) ) return 0.0;
		$coupon = Coupon::get_by_id($this->coupon_id);
		if( !$coupon || !$coupon->validate($this->get_event()) ) return 0.0;
		
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
	
	function approve($email = true, $ignore_spaces = false) : bool {
		$transaction = new Transaction(
			type: TransactionType::Sale,
			amount: $this->get_price(),
			gateway: $this->gateway,
			id: ''
		);
		$this->transactions->add($transaction);

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
		
		if( $this->email() && $this->mails_sent > 0 ){
			return true;
		}

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
	
	function email_send($subject, $body, $email, $attachments = array()) : bool {
		
		$mailer = $this->app()->get(Mailer::class);
		if(empty($subject)) return false;

		if( !$mailer->send($subject, $body, $email, $attachments) ) {
			if( is_array($mailer->errors) ){
				foreach($mailer->errors as $error){
					$this->errors[] = $error;
				}
			}else{
				$this->errors[] = $mailer->errors;
			}
			return false;
		}
		
		return true;
	}

	function email( bool $email_admin = true, bool $force_resend = false, bool $email_attendee = true ) : bool
	{
		$result = true;
		$this->mails_sent = 0;
		
		
		//Make sure event matches booking, and that booking used to be approved.
		if( $this->status !== $this->previous_status || $force_resend ){

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
				
				if( !$this->email_send( $msg['user']['subject'], $msg['user']['body'], $this->user_email, $attachments) ){
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
	    switch( $this->status ){
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
			'coupon' => $this->coupon_id ? Coupon::get_by_id($this->coupon_id)?->jsonSerialize() : null,
			'notes' => $this->notes->jsonSerialize(),
			'log' => $this->log->jsonSerialize(),
			'attendees' => $this->attendees,
			'registration' => $this->registration,
		];
	}

}