<?php
// Exit if accessed directly

namespace Contexis\Events\Payment\Gateways;

use Contexis\Events\Models\Booking;
use Contexis\Events\Models\BookingStatus;
use Contexis\Events\Options;
use Contexis\Events\Payment\Gateway;
use Contexis\Events\Views\BookingView;
use Mollie\Api\MollieApiClient;
use WP_REST_Request;

if (!defined('ABSPATH')) exit;


/**
 * Configure Mollie Gateway for Events Manager Pro.
 */
Class GatewayMollie extends Gateway implements \JsonSerializable {

	public string $slug = 'mollie';
	public string $title = '';
	public string $description = '';
	public string $feedback = '';
	public BookingStatus $status = BookingStatus::AWAITING_ONLINE_PAYMENT;
	public string $status_txt = 'Awaiting Online Payment';
	public bool $payment_return = true;

	public array $allowed_options = array(
		'title',
		'description',
		'feedback',
		'api_key',
		'message_free',
		'message_redirect',
		'return_page',
		'show_status',
		'status_text',
		'show_feedback',
		'description',
		'send_cancel_mail',
	);

	public array $transaction_detail = array(
			'Mollie Dashboard',
			'https://www.mollie.com/dashboard/payments/%s',
			'https://www.mollie.com/dashboard/payments/%s'
		);

	private ?MollieApiClient $mollie;
	
	public function __construct() {
		parent::__construct();
		$this->mollie = self::start_mollie();
		$this->title = ($desc = get_option('em_'.$this->slug.'_title')) !== '' ? $desc : __('Mollie', 'events');
		$this->description = ($desc = get_option('em_'.$this->slug.'_description')) !== '' ? $desc : __('Mollie is a payment service provider that allows you to accept online payments in your store.', 'events');


		// Check if the gateway is activated (= toggled).
		if( parent::is_active() ) {
			add_filter('em_booking_validate', array($this, 'booking_validate'), 2, 2);
		}
		add_filter('the_content', array($this, 'handle_mollie_customer_return'));
		add_action('rest_api_init', array($this, 'register_rest_routes'));
	}

	function jsonSerialize() : array {
		$fields = parent::jsonSerialize();
		$fields["methods"] = GatewayMollie::get_methods();
		return $fields;
	}

	function get_available_status() {
		return array(
			BookingStatus::APPROVED => __('Approved', 'events'),
			BookingStatus::REJECTED => __('Rejected', 'events'),
			BookingStatus::CANCELED => __('Cancelled', 'events'),
			BookingStatus::AWAITING_ONLINE_PAYMENT => __('Waiting for Mollie', 'events'),
			BookingStatus::PAYMENT_FAILED => __('Failed', 'events'),
		);
	}




	/**
	 * Hook into booking validation and check validate payment type if present.
	 *
	 * @param boolean $result
	 * @param Booking $booking
	 * @return boolean
	 */
	function booking_validate($result, $booking) {
		$api_key = get_option('em_mollie_api_key');
		if( !isset($api_key) || empty($api_key) ) {
			$booking->errors[] =   __('Mollie API Key is not found.', 'events');
			$result = false;
		}

		return $result;
	}

	
	function get_payment_info( Booking $booking ) : array {
	
		$result = array(
			'type' => 'online',
			'message' => get_option('em_mollie_message_redirect', __('Redirecting to complete your online payment...', 'events')),
			'data' => []
		 );

		if ($booking->get_price() == 0 ) {
			$result['message'] 	= get_option('em_mollie_message_free', __('This booking is free, no payment is required.', 'events'));
			return $result;
		}

		$description = sprintf( __('%s tickets for %s', 'events'), $booking->get_booked_spaces(), $booking->get_event()->event_name );
		
		$args = [
			'amount'  		=> [
				'currency' 		=> strtoupper( get_option('dbem_bookings_currency') ),
				'value'   		=> number_format( $booking->get_price(), 2)
			],
			'description' 	=> $description,
			'redirectUrl' 	=> $this->get_mollie_return_url($booking) . "?em_mollie_return={$booking->id}",
			'webhookUrl' 	=> $this->get_payment_return_url(),
			'locale'   		=> get_locale(),
			'sequenceType'  => 'oneoff',  			
			'metadata'  	=> [
				'booking_id' 	=> $booking->id,
				'name'    		=> $booking->get_full_name(),
				'email'   		=> $booking->user_email,
				'event'   		=> $booking->get_event()->event_name,
				'tickets' 		=> $booking->get_booked_spaces(),
			],
		];
		
		$request = $this->mollie->payments->create( $args );

		$result['data']['link'] = $request->getCheckoutUrl();
		$result['data']['expires_at'] = $request->expiresAt;
		$result['data']['id'] = $request->id;
		$result['data']['created_at'] = $request->createdAt;

		return $result;
	}


	/**
	 * Determine the redirect url after Mollie payment.
	 *
	 * @return string URL
	 */
	function get_mollie_return_url($booking) {
		$event = $booking->get_event();

		 if( get_option('em_mollie_return_page') ){
			 return get_permalink(get_option( 'em_mollie_return_page') );
		 }
		 return get_permalink($event->event_id);
	}


	/**
	 * Handle content when a user returns from Mollie after payment.
	 *
	 * @param string $content
	 * @return string Page content
	 */
	function handle_mollie_customer_return( $content ) {
	 	if( strpos($_SERVER['REQUEST_URI'], 'em_mollie_free') !== false ) {
			$content = sprintf( '<p><div class="em-booking-message em-booking-message-success">%s</div></p>', get_option('em_mollie_message_free'));
			return $content;
		}

		if( strpos($_SERVER['REQUEST_URI'], 'em_mollie_return') !== false ) {
			$class 			= null;
			$feedback 		= null;
			$result 		= null;
			$booking_id 	= absint($_REQUEST['em_mollie_return']);
			$booking 		= Booking::get_by_id($booking_id);
			$status 		= (int) $booking->status->value;

			$payment_status = array(
				0 => __("Pending", 'events'),
				1 => __("Approved", 'events'),
				2 => __("Rejected", 'events'),
				3 => __("Cancelled", 'events'),
				4 => __("Waiting for Mollie", 'events'),
				5 => __("Pending", 'events'),
			);

			switch( $status ) {
				case 1: 	// Approved
					$class 		= 'success';
					$feedback 	= get_option('dbem_booking_feedback');
				break;
				case 3:		// Cancelled
				case 2: 	// Reject = fallback.
					$class 		= 'error';
					$feedback 	= __('Booking could not be created','events');
				break;
				case 0: 	// Pending/Open
				case 4: 	// Awaiting Online Payment.
				case 5: 	// Awaiting Payment.
					$class 		= 'warning';
					$feedback 	= get_option('dbem_booking_feedback_pending');
					// Add styling for this status only - use EM css for the others.
				break;
			}
			$status_string 	= get_option('em_mollie_status_text') ?? __('The status of your payment is', 'events');
			$status_text 	= sprintf('<h3 class="alert__title">%s: %s</h3>', $status_string, strtoupper($payment_status[$status]) );
			$status_text 	= get_option('em_mollie_show_status') != 'no' ? $status_text : null;
			$feedback_text 	= get_option('em_mollie_show_feedback') != 'no' ? $feedback	: null;
			$button 		= sprintf('<div class="button-group button-group--right"><a class="button button--primary" href=%s>%s</a></div>',
				esc_url(get_permalink(get_option('dbem_events_page'))), esc_attr__('Continue', 'events')	);

			$result 	= sprintf('<section class="section py-12 %s" style="max-width: 33%%;">', $class);
			$result 	.= '<div class="card card--shadow bg-white card__image-top">';
			$result 	.= '<div class="card__content"><div class="card__title">' . $status_text . '</div><div class="card__text">' . $feedback_text . '</div>';
			$result		.= $button;
			$result		.= '</div></div></section>';

			$content = apply_filters('em_mollie_payment_feedback', $result);
			return $content;
		}
		return $content;
	}


	/**
	 * When Mollie calls the webhook, update database, update Booking Status & send emails.
	 *
	 * @return void
	 */
	public function handle_payment_return_api(WP_REST_Request $request) : \WP_REST_Response {
		$params = $request->get_params();
		if( !isset($params['em_payment_gateway']) || $params['em_payment_gateway'] != 'mollie' || !isset($params['id']) ) {
			return new \WP_REST_Response( array('message' => 'Invalid request'), 400 );
		}

		$mollie_id = trim( $params['id'] );

		if( !is_object($this->mollie) ) return new \WP_REST_Response( array('message' => 'Mollie not initialized'), 500 );

		$payment 	= $this->mollie->payments->get($mollie_id);
		$timestamp  = date('Y-m-d H:i:s', strtotime($payment->createdAt));
		$booking_id = $payment->metadata->id;
		$booking	= Booking::get_by_id($booking_id);
		$note 		= ' ';

		if (empty( $booking->id )) {
			return new \WP_REST_Response( array('message' => 'Booking not found'), 404 );
		}

		if ($payment->isPaid() && !$payment->hasRefunds() && !$payment->hasChargebacks()) {
			$this->record_transaction( $booking, $payment->amount->value, strtoupper($payment->amount->currency), $timestamp, $mollie_id, 'Completed', $note );
			$booking->approve(true, true);
		}

		elseif ($payment->isOpen() || $payment->isPending()) {
			$booking->set_status(BookingStatus::AWAITING_ONLINE_PAYMENT, true);
		}

		elseif ($payment->isCanceled() || $payment->isFailed() || $payment->isExpired()) {
			// Mollie uses US spelling.
			$payment->status = ($payment->status != 'canceled') ? $payment->status : 'cancelled';
			$this->record_transaction( $booking, $payment->amount->value, strtoupper($payment->amount->currency), $timestamp, $mollie_id, 'Canceled', $note );
			$send_mail = get_option('em_mollie_send_cancel_mail') != 'yes' ? false : true;
			$booking->set_status(BookingStatus::CANCELED, $send_mail);
		}

		elseif ($payment->hasChargebacks()) {
			$note = __('Charged back', 'events');
			$this->record_transaction( $booking, $payment->amount->value, strtoupper($payment->amount->currency), $timestamp, $mollie_id, 'Charded back', $note);
			$booking->set_status(BookingStatus::CANCELED, true);
		}
		elseif ($payment->hasRefunds()) {
			// Fetch detailed info for refund from Mollie.
			foreach( $payment->refunds() as $refund ) {
				$date 		= $this->get_localized_time($refund->createdAt);
				$note 		= sprintf( __('Refunded on %s', 'events'), $date );
				$this->record_transaction( $booking, $payment->amountRefunded->value, strtoupper($payment->amount->currency), $timestamp, $mollie_id, 'Refunded', $note);
			}
		}

		do_action('em_payment_processed', $booking, $this);
		
		return new \WP_REST_Response( array('message' => 'Payment processed'), 200 );
	}


	

	static function start_mollie() : ?MollieApiClient {
		$api_key = get_option('em_mollie_api_key', false);
		if(!$api_key) return null;
		$mollie = new \Mollie\Api\MollieApiClient();	
		$mollie->setApiKey( $api_key );
		return $mollie;
	}

	function get_localized_time( string $input ) : string {
		$UTC 	= new \DateTimeZone("UTC");
		$newTZ 	= new \DateTimeZone( get_option('timezone_string') );
		$date 	= new \DateTime( date("Y-m-d H:i:s", strtotime($input)), $UTC );
		$date->setTimezone( $newTZ );
		$result = $date->format('Y-m-d H:i:s');
		return $result;
	}

	public static function translate( string $string ) : string {

		$translate 	= array(
			'open'			=> __('open', 'events'),
			'pending' 		=> __('pending', 'events'),
			'paid' 			=> __('paid', 'events'),
			'canceled' 		=> __('canceled', 'events'),
			'expired' 		=> __('expired', 'events'),
			'failed' 		=> __('failed', 'events'),
			'refunded' 		=> __('refunded', 'events'),
			'chargeback' 	=> __('chargeback', 'events'),
		);

		return $translate[$string];
	}

	public static function mollie_method( string $method ) : array {
		$names = [
			'applepay' 	    => __('Apple Pay', 'events'),
			'googlepay' 	=> __('Google Pay', 'events'),
			'bancontact' 	=> __('Bancontact', 'events'),
			'creditcard' 	=> __('Credit Card', 'events'),
			'directdebit' 	=> __('Direct Debit', 'events'),
			'eps' 			=> __('EPS', 'events'),
			'giftcard' 		=> __('Gift Card', 'events'),
			'googlepay' 	=> __('Google Pay', 'events'),
			'ideal' 		=> __('iDEAL', 'events'),
			'klarna' 		=> __('Klarna', 'events'),
			'paypal' 		=> __('PayPal', 'events'),
			'postfinance' 	=> __('PostFinance', 'events'),
			'sofort' 		=> __('SOFORT Banking', 'events'),
			'swish' 		=> __('Swish', 'events'),
			'twintr' 		=> __('Twint', 'events'),
		];

		$descriptions = [
			'applepay'  	=> __('Pay with your Apple ID', 'events'),
			'googlepay' 	=> __('Pay with your Google Account', 'events'),
			'bancontact' 	=> __('Digital Payment Service', 'events'),
			'creditcard' 	=> __('Mastercard, VISA, Amex', 'events'),
			'directdebit' 	=> __('Vpay or Maestro', 'events'),
			'eps' 			=> __('Austrian Payment Service', 'events'),
			'giftcard' 		=> __('Gift Card', 'events'),
			'googlepay' 	=> __('Pay with your Google Account', 'events'),
			'ideal' 		=> __('iDEAL', 'events'),
			'klarna' 		=> __('Klarna', 'events'),
			'paypal' 		=> __('PayPal', 'events'),
			'postfinance' 	=> __('PostFinance', 'events'),
			'sofort' 		=> __('Transfer Money from Your Account within Seconds', 'events'),
			'swish' 		=> __('Swish', 'events'),
			'twintr' 		=> __('Swiss payment service', 'events'),
		];

		return [
			'name' 		=> $names[$method],
			'description' => $descriptions[$method],
		];
		
	}

	function mollie_status( string $status ) : string {
		return $this->translate($status);
	}

	static function get_methods() : array {
		$mollie = self::start_mollie();
		if( !is_object($mollie) ) return [];

		$methods = get_option('mollie_activated_methods');

		if( $methods ) return $methods;

		$methods	= array();
		$all 		= $mollie->methods->allActive(['locale' => get_locale(), 'includeWallets' => 'applepay,googlepay']);
		foreach( $all as $method ) {

			$texts = self::mollie_method($method->id);
			$methods[$method->id] = [
				'name' => $texts['name'],
				'description' => $texts['description'],
				'image' => plugin_dir_url( __FILE__ ) . 'icons/' . $method->id . '.svg',
			];

		}

		return $methods;
	}

	public function refresh_methods() : array {
		delete_option('mollie_activated_methods');
		$methods = $this->get_methods();
		update_option('mollie_activated_methods', $methods);
		return $methods;
	}

	public function register_rest_routes() {
		register_rest_route( 'em-mollie/v2', '/methods', array(
			'methods' 	=> 'GET',
			'callback' 	=> array($this, 'get_methods'),
			'permission_callback' => '__return_true',
		));

		register_rest_route( 'em-mollie/v2', '/refresh', array(
			'methods' 	=> 'GET',
			'callback' 	=> array($this, 'refresh_methods'),
			'permission_callback' => '__return_true',
		));

		register_rest_route( 'events/v2', '/mollie/webhook', array(
			'methods' 	=> 'GET',
			'callback' 	=> array($this, 'handle_payment_return'),
			'permission_callback' => '__return_true',
		));
	}

	public function get_settings_fields() : array {
		$settings = parent::get_settings_fields();
		$gateway_options = [
			[
				'label' => __('API Key', 'events'),
				'id' => 'api_key',
				'type' => 'text',
				'help' => __('Obtain your Live or Test API Key from your <a href=%s target="_blank">Mollie Dashboard</a>.', 'events'),
				'placeholder' => '',
				'value' => get_option('em_'.$this->slug.'_api_key', $this->title),
			],
			[
				'label' => __('Free Booking Message', 'events'),
				'id' => 'message_free',
				'type' => 'textarea',
				'help' => __('Shown when the total booking price is 0.00. Your customer will <b>not</b> be redirected to Mollie.', 'events'),
				'value' => get_option('em_'.$this->slug.'_message_free', __('Thank you for your booking.<br>You will receive a confirmation email soon.', 'events')),
			],
			[
				'label' => __('Redirect Message', 'events'),
				'id' => 'message_redirect',
				'type' => 'textarea',
				'help' => __('Shown when the booking is successfully created and the customer is redirected to Mollie.', 'events'),
				'value' => get_option('em_'.$this->slug.'_message_redirect', __('Redirecting to complete your online payment...', 'events')),
			],
			[
				'label' => __('Return Page', 'events'),
				'id' => 'return_page',
				'type' => 'page_select',
				'help' => __('Your customer will be redirected back to this page after the payment. Leave blank to use the Single Event Page.', 'events'),
				'value' => get_option('em_'.$this->slug.'_return_page', 0),
			],
			[
				'label' => __('Send email on failed / cancelled payment?', 'events'),
				'id' => 'send_cancel_mail',
				'type' => 'toggle',
				'default' => 'yes',
				'help' => __('By default Events Manager will send the Booking Cancelled Email if a payment had failed or is incomplete. This can lead to confusion if the user rebooks right after with a successful payment. This option lets you disable sending the automatic Booking Cancelled Email. (Setting this option to "no" will not affect the email if you change the booking status manually.)', 'events'),	
			]


		];
		return array_merge($settings, $gateway_options);
		
	} 

}
