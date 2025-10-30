<?php

namespace Contexis\Events\Payment\Gateways;
use Contexis\Events\Payment\Gateway;

use Contexis\Events\Collections\BookingCollection;
use Contexis\Events\Models\Booking;
use Contexis\Events\Models\BookingStatus;
use Contexis\Events\Options;
use Contexis\Events\Models\Event;
use Contexis\Events\Payment\GenerateQRCode;
use Contexis\Events\Repositories\BookingRepository;

class GatewayOffline extends Gateway {

	public string $slug = 'offline';
	public string $title = '';
	public string $description = '';
	public string $feedback = '';
	public BookingStatus $status = BookingStatus::AWAITING_PAYMENT;

	public array $allowed_options = [
		'title', 'description', 'feedback', 'iban', 'bic', 'bank', 'beneficiary', 'deadline'
	];
	
	function __construct() {
		parent::__construct();
		$this->title = get_option('em_'.$this->slug.'_title', __('Offline Payment', 'events'));
		$this->description = get_option('em_'.$this->slug.'_description', __('Pay with cash or bank transfer.','events'));
		add_action('rest_api_init', array($this, 'register_rest_route'));
		add_filter('em_booking_set_status',array(&$this,'em_booking_set_status'),1,2);
		add_filter('em_bookings_pending_count', array(&$this, 'em_bookings_pending_count'),1,1);
		add_filter('em_booking_validate', array(&$this,'em_booking_validate'),9,2); 
	}

	function get_payment_info( Booking $booking ) : array {
		if(!get_option("em_offline_iban", true)) return [
			'success' => false,
			'error' => "No IBAN available. Please add an IBAN in the offline payment gateway"
		];
		
		$event = Event::get_by_id($booking->event_id);

		$result = [
			"type" => "offline",
			"data" => [
				"purpose" => $booking->id . "-" . $event->event_id . "-" . $booking->get_last_name(),
				"iban" => get_option("em_offline_iban", true),
				"beneficiary" => get_option("em_offline_beneficiary", true),
				"bic" => get_option("em_offline_bic", true),
				"bank" => get_option("em_offline_bank", true),
				"amount" => $booking->full_price,
				"deadline" => get_option("em_offline_deadline", true),
				"title" => $this->title,
				"message" => get_option("em_offline_booking_feedback", true)
			]
		];

		return $result;
	}

	function em_booking_set_status($result, $booking){
		if($booking->booking_status == BookingStatus::APPROVED && $this->uses_gateway($booking) && (empty($_REQUEST['action']) || $_REQUEST['action'] != 'gateway_add_payment') ){
			$this->record_transaction($booking, $booking->get_price(false,false,true), get_option('dbem_bookings_currency'), current_time('mysql'), '', 'Completed', '');								
		}
		return $result;
	}

	
	function em_bookings_pending_count($count){
		$booking_collection = BookingRepository::sum_spaces(0, [BookingStatus::AWAITING_PAYMENT]);
		return $count + $booking_collection;
	}
	
	
	

	
	function em_booking_validate(bool $result, Booking $booking){
		return $result;
	}

	
	
	function get_settings_fields() : array {
		$settings = parent::get_settings_fields();
		$gateway = array(
			[
				'label' => __('IBAN', 'events'),
				'id' => 'iban',
				'type' => 'text',
				'help' => __('In order to generate a QR Code for payment, you have to provide a valid IBAN','events'),
				'placeholder' => '',
				'value' => get_option('em_'.$this->slug.'_iban', $this->title),
			],
			[
				'label' => __('BIC', 'events'),
				'id' => 'bic',
				'type' => 'text',
				'help' => __('Some banks require their customers to provide a BIC in order to transfere money','events'),
				'placeholder' => '',
				'value' => get_option('em_'.$this->slug.'_bic', $this->description),
			],
			[
				'label' => __('Name of the bank', 'events'),
				'id' => 'bank',
				'type' => 'text',
				'help' => __('', 'events'),
				'placeholder' => '',
				'value' => get_option('em_'.$this->slug.'_bank'),
			],
			[
				'label' => __('Beneficiary', 'events'),
				'id' => 'beneficiary',
				'type' => 'text',
				'help' => __('In some countries you need to specify a beneficiary. This Data is added to the QR Code.','events'),
				'placeholder' => '',
				'value' => get_option('em_'.$this->slug.'_beneficiary'),
			],
			[
				'label' => __('Payment Deadline', 'events'),
				'id' => 'deadline',
				'type' => 'number',
				'help' => __('Number of days until payment has to be made','events'),
				'placeholder' => '',
				'value' => get_option('em_'.$this->slug.'_deadline'),
			],
		);

		$settings = array_merge($settings, $gateway);
		return $settings;
	}
	
	function uses_gateway($booking){
		return parent::uses_gateway($booking) || ( empty($booking->gateway) && $booking->status->value == $this->status );
	}


	function register_rest_route() {
		register_rest_route( 'events/v2', '/gateway/offline', [
			'methods' => \WP_REST_Server::CREATABLE,
			'callback' => [$this, 'add_payment'],
			'permission_callback' => function () {
    			return current_user_can('edit_posts');
			}
		]);
	}


	function add_payment($request) {
		$booking = Booking::get_by_id(absint($request['booking_id']));
		if( !empty($request['transaction_total_amount']) && is_numeric($request['transaction_total_amount']) ){
			$this->record_transaction($booking, $_REQUEST['transaction_total_amount'], get_option('dbem_bookings_currency'), current_time('mysql'), '', 'Completed', $_REQUEST['transaction_note']);
			$total = $booking->get_total_paid();
			if( $total >= $booking->get_price() ){
				$booking->approve();
			}
			do_action('em_payment_processed', $booking, $this);
		}
	}

}


GenerateQRCode::init();

?>