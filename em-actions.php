<?php

use Contexis\Events\Models\Booking;

/**
 * Performs actions on init. This works for both ajax and normal requests, the return results depends if an em_ajax flag is passed via POST or GET.
 * 
 * @TODO: This whole file must be split up and the wp_ajax_ functions should be replaced with the REST API where possible
 */
function em_init_actions() {
	global $EM_Notices; 
	if( defined('DOING_AJAX') && DOING_AJAX ) $_REQUEST['em_ajax'] = true;
	
	//Event Actions
	if( !empty($_REQUEST['action']) && substr($_REQUEST['action'],0,5) == 'event' ){
		//Load the event object, with saved event if requested
		
		
	}

	if( !empty($_REQUEST['action']) && substr($_REQUEST['action'],0,7) == 'booking' && (is_user_logged_in() || ($_REQUEST['action'] == 'booking_add')) ){
		
		$event_id = !empty($_REQUEST['event_id']) ? $_REQUEST['event_id'] : 0;
		$event = \Contexis\Events\Models\Event::get_by_id($event_id);
		
		$booking = ( !empty($_REQUEST['booking_id']) ) ? Booking::get_by_id(absint($_REQUEST['booking_id'])) : new Booking;
		if( !empty($booking->event_id) ){
			//Load the event object, with saved event if requested
			$event = $booking->get_event();
		}elseif( !empty($_REQUEST['event_id']) ){
			$event = \Contexis\Events\Models\Event::get_by_id($_REQUEST['event_id']);
		}
		$result = false;
		$feedback = '';
		
		if( $_REQUEST['action'] == 'booking_resend_email' ){
			if(!current_user_can('edit_pages')) return;
			
				if( $booking->email(false, true) ){
				    if( $booking->mails_sent > 0 ) {
				        $EM_Notices->add_confirm( __('Email Sent.','events'), true );
				    }else{
				        $EM_Notices->add_confirm( _x('No emails to send for this booking.', 'bookings', 'events'), true );
				    }
					$redirect = !empty($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : wp_validate_redirect(wp_get_raw_referer(), false );
					wp_safe_redirect( $redirect );
					exit();
				}else{
					$result = false;
					$EM_Notices->add_error( __('ERROR : Email Not Sent.','events') );			
					$feedback = $booking->feedback_message;
				}	
			
		}

		
		
		//wp_die();
	}

		
	//EM Ajax requests require this flag.
	if( is_user_logged_in() ){
		//Admin operations
		//Specific Oject Ajax
		if( !empty($_REQUEST['em_obj']) && $_REQUEST['em_obj'] == 'em_bookings_events_table' ){
			include_once('admin/bookings/em-events.php');
			em_bookings_events_table();
			exit();
		}
	}

}
add_action('init','em_init_actions',11);

