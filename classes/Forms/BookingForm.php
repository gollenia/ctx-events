<?php

namespace Contexis\Events\Forms;

use Contexis\Events\Forms\Form;
use Contexis\Events\Models\Booking;
use Contexis\Events\Models\Event;


class BookingForm {
	static $validate;
	/**
	 * @var EM_Form
	 */
	static $form;
	static $form_id; 
	static $event_id;
	static $form_name;
	static $form_template;
	
	public static function init(){	
				
		$instance = new self;
		$booking_button_request = !empty($_REQUEST['action']) && $_REQUEST['action'] == 'booking_add_one' && is_user_logged_in(); //in order to disable the form if booking button is pressed
		if( !$booking_button_request ){
			//add_filter('em_booking_get_post', array($instance, 'em_booking_get_post'), 10, 2); //get post data + validate
			add_filter('em_booking_validate', array($instance, 'em_booking_validate'), 10, 2); //validate object
		}
	
	}
	
	/**
	 * Gets the default form structure for creating a new form
	 * @return array
	 */
	public static function get_form_template(){
	    if( empty(self::$form_template )){
    		self::$form_template = apply_filters('em_booking_form_get_form_template', array (
    			'first_name' => array ( 'label' => __('First Name','events'), 'type' => 'name', 'fieldid'=>'user_name', 'required'=>1 ),
				'last_name' => array ( 'label' => __('Last Name','events'), 'type' => 'name', 'fieldid'=>'last_name', 'required'=>1 ),
    			'user_email' => array ( 'label' => __('Email','events'), 'type' => 'email', 'fieldid'=>'user_email', 'required'=>1 ),
    		  	'booking_comment' => array ( 'label' => __('Comment','events'), 'type' => 'textarea', 'fieldid'=>'booking_comment' ),
    		));        
	    }
	    return self::$form_template;
	}
	
	/**
	 * @param Booking $booking
	 */
	public static function get_form( $event = false ){
	    //make sure we don't need to get another form rather than the one already stored in this object
		if(!empty(self::$form)) return self::$form;

		if(is_numeric($event)){ $event = Event::get_by_id($event); }

		self::$form_id = $event ? get_post_meta($event->post_id, '_booking_form', true) : 0;

		$form_data = Form::get_form_data(self::$form_id);

		if(empty($form_data)) {
			$form_data = array('form' => self::get_form_template());
			self::$form_name = __('Default','events');
		}
		self::$form_name = get_the_title(self::$form_id);
		self::$form = new Form($form_data, 'em_bookings_form');
		self::$form->form_required_error = __('Please fill in the field: %s','events');

		return self::$form;
	}

	

	public static function get_booking_form($event_id){
		$form_id = get_post_meta($event_id, '_booking_form', true);
		$form_data = Form::get_form_data($form_id, false);
		return $form_data;
	}

	public static function em_booking_validate(bool $result, Booking $booking) : bool {
		$EM_Form = self::get_form($booking->event_id, $booking);

		if( empty($EM_Form->field_values) ){
		    $EM_Form->field_values = $booking->registration;
		}
	
		if( !$EM_Form->validate() ){
		    $booking->errors[] = $EM_Form->get_errors();
			return false;
		}

		return $result;
	}
	
}

BookingForm::init();
include('AttendeesForm.php');

