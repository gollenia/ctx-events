<?php

use Contexis\Events\Models\Event;

class EM_Booking_Form {
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
				
		//Booking admin and exports
		
		//Booking Tables UI
		add_filter('em_bookings_table_rows_col', array('EM_Booking_Form','em_bookings_table_rows_col'),10,5);
		add_filter('em_bookings_table_cols_template', array('EM_Booking_Form','em_bookings_table_cols_template'),10,2);
		// Actions and Filters
		add_filter('em_booking_form_custom', array('EM_Booking_Form','booking_form'),10,1); //handle the booking form template
        add_filter('em_booking_form_custom_json', array('EM_Booking_Form','booking_form_json'),10,1); //handle the booking form template
		//Booking interception
		$booking_button_request = !empty($_REQUEST['action']) && $_REQUEST['action'] == 'booking_add_one' && is_user_logged_in(); //in order to disable the form if booking button is pressed
		if( !$booking_button_request ){
			add_filter('em_booking_get_post', array('EM_Booking_Form', 'em_booking_get_post'), 10, 2); //get post data + validate
			add_filter('em_booking_validate', array('EM_Booking_Form', 'em_booking_validate'), 10, 2); //validate object
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
	 * @param EM_Booking $EM_Booking
	 */
	public static function get_form( $event = false ){
	    //make sure we don't need to get another form rather than the one already stored in this object
		if(!empty(self::$form)) return self::$form;

		if(is_numeric($event)){ $event = Event::find_by_event_id($event); }

		self::$form_id = $event ? get_post_meta($event->post_id, '_booking_form', true) : 0;

		$form_data = EM_Form::get_form_data(self::$form_id);

		if(empty($form_data)) {
			$form_data = array('form' => self::get_form_template());
			self::$form_name = __('Default','events');
		}
		self::$form_name = get_the_title(self::$form_id);
		self::$form = new EM_Form($form_data, 'em_bookings_form');
		self::$form->form_required_error = __('Please fill in the field: %s','events');

		return self::$form;
	}

	

	public static function get_booking_form($event_id){
		$form_id = get_post_meta($event_id, '_booking_form', true);
		$form_data = EM_Form::get_form_data($form_id, false);
		return $form_data;
	}

	
	
	
	/*
	public static function booking_form($event = false){
		gnobal $event;
        $event = empty($event) ? $event : $event;
        echo self::get_form($event);
	}

    public static function booking_form_json($event = false){
		gnobal $event;
        $event = empty($event) ? $event : $event;
        return self::get_form($event)->form_fields;
	}
	*/
	
	/**
	 * @param boolean $result
	 * @param EM_Booking $EM_Booking
	 * @return bool
	 */
	public static function em_booking_get_post($result, $EM_Booking){
		//get, store and validate post data 
		$EM_Form = self::get_form($EM_Booking->event_id, $EM_Booking);
        //skip registration fields if manually booking someone that already is a user
		$manual_assigned_booking = false;		
		//get form fields
		if( $EM_Form->get_post() ){
			foreach($EM_Form->get_values() as $fieldid => $value){
				
				if($fieldid == 'user_password'){
				    $EM_Booking->temporary_password = $value; //assign a random property so it's never saved
				}else{
					//get results and put them into booking meta
					if( !$manual_assigned_booking && (array_key_exists($fieldid, $EM_Form->user_fields) || in_array($fieldid, array('user_email','user_name'))) ){
					    if( !(!empty($EM_Booking->booking_id) && $EM_Booking->can_manage()) || empty($EM_Booking->booking_id) ){ //only save reg fields on first go
							//registration fields
							
							$EM_Booking->booking_meta['registration'][$fieldid] = $value;
					    }
					}else{ //ignore captchas, only for verification
						//booking fields
						$EM_Booking->booking_meta['booking'][$fieldid] = $value;
					}
				}
			}
		}elseif( count($EM_Form->get_errors()) > 0 ){
			$result = false;
			$EM_Booking->add_error($EM_Form->get_errors());
		}
		return $result;
	}
	
	/**
	 * @param boolean $result
	 * @param EM_Booking $EM_Booking
	 * @return boolean
	 */
	public static function em_booking_validate($result, $EM_Booking){
		$EM_Form = self::get_form($EM_Booking->event_id, $EM_Booking);

		if( empty($EM_Form->field_values) ){
		    //in the event we're validating a booking that wasn't retrieved by post, with booking meta
		    $values = array();
		    if( !empty($EM_Booking->booking_meta['booking']) ){
		        $values = $EM_Booking->booking_meta['booking'];
		    }
		    if( !empty($EM_Booking->booking_meta['registration']) ){
		    	$values = array_merge($values, $EM_Booking->booking_meta['registration']);
		    }
		    $EM_Form->field_values = $values;
		}
		if( !empty($EM_Booking->mb_validate_bookings) ) $EM_Form->ignore_captcha = true; //MB Mode doing a final validation, so no need to re-check captcha
		if( !$EM_Form->validate() ){
		    $EM_Booking->add_error($EM_Form->get_errors());
			return false;
		}
		if( !empty($EM_Booking->mb_validate_bookings) ) unset($EM_Form->ignore_captcha);  //MB Mode doing a final validation, so no need to re-check captcha
		return $result;
	}
	
	

	public static function em_bookings_table_rows_col($column, $EM_Booking, $format){
		$EM_Form = self::get_form($EM_Booking->get_event()->event_id ?? null);

		if (!$EM_Form->is_normal_field($column) || !array_key_exists($column, $EM_Booking->booking_meta['booking'] ?? [])) {
			return '';
		}
		$field = $EM_Form->form_fields[$column];
    	$value = $EM_Form->get_formatted_value($field, $EM_Booking->booking_meta['booking'][$column]);

    	return ($format == 'html' || empty($format)) ? esc_html($value) : $value;
	}
	
	public static function em_bookings_table_cols_template($template, $EM_Bookings_Table){
		$event = $EM_Bookings_Table->event;
		$event_id = (!empty($event->event_id)) ? $event->event_id:false;
		$EM_Form = self::get_form($event_id);
		foreach($EM_Form->form_fields as $field_id => $field ){
		    if( $EM_Form->is_normal_field($field_id) ){ //user fields already handled, htmls shouldn't show
    			$template[$field_id] = $field['label'] ?? '';
		    }
		}
		return $template;
	}
	
}

EM_Booking_Form::init();
include('AttendeeForms.php');

