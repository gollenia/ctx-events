<?php
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
		if( !empty($_REQUEST['event_id']) ){
			$event = \Contexis\Events\Models\Event::find_by_event_id($_REQUEST['event_id']);
		}else{
			$event = new \Contexis\Events\Models\Event;
		}
		
		if( $_REQUEST['action'] == 'event_detach' && wp_verify_nonce($_REQUEST['_wpnonce'],'event_detach_'.get_current_user_id().'_'.$event->event_id) ){ 
			//Detach event and move on
			if($event->detach()){
				$EM_Notices->add_confirm( $event->feedback_message, true );
			}else{
				$EM_Notices->add_error( $event->errors, true );			
			}
			wp_safe_redirect(wp_validate_redirect(wp_get_raw_referer(), false ));
			exit();
		}elseif( $_REQUEST['action'] == 'event_attach' && !empty($_REQUEST['undo_id']) && wp_verify_nonce($_REQUEST['_wpnonce'],'event_attach_'.get_current_user_id().'_'.$event->event_id) ){ 
			//Detach event and move on
			if( $event->attach( absint($_REQUEST['undo_id']) ) ){
				$EM_Notices->add_confirm( $event->feedback_message, true );
			}else{
				$EM_Notices->add_error( $event->errors, true );
			}
			wp_safe_redirect(wp_validate_redirect(wp_get_raw_referer(), false ));
			exit();
		}
		
		//AJAX Exit
		if( isset($events_result) && !empty($_REQUEST['em_ajax']) ){
			if( $events_result ){
				$return = array('result'=>true, 'message'=>$event->feedback_message);
			}else{		
				$return = array('result'=>false, 'message'=>$event->feedback_message, 'errors'=>$event->errors);
			}
			echo json_encode($return);
			exit();
		}
	}

	if( !empty($_REQUEST['action']) && substr($_REQUEST['action'],0,7) == 'booking' && (is_user_logged_in() || ($_REQUEST['action'] == 'booking_add')) ){
		
		$event_id = !empty($_REQUEST['event_id']) ? $_REQUEST['event_id'] : 0;
		$event = \Contexis\Events\Models\Event::find_by_event_id($event_id);
		global $EM_Booking;
		//Load the booking object, with saved booking if requested
		$EM_Booking = ( !empty($_REQUEST['booking_id']) ) ? EM_Booking::find($_REQUEST['booking_id']) : EM_Booking::find();
		if( !empty($EM_Booking->event_id) ){
			//Load the event object, with saved event if requested
			$event = $EM_Booking->get_event();
		}elseif( !empty($_REQUEST['event_id']) ){
			$event = \Contexis\Events\Models\Event::find_by_event_id($_REQUEST['event_id']);
		}
		$result = false;
		$feedback = '';
		
		if( $_REQUEST['action'] == 'booking_resend_email' ){
			if( $EM_Booking->can_manage('manage_bookings','manage_others_bookings') ){
				if( $EM_Booking->email(false, true) ){
				    if( $EM_Booking->mails_sent > 0 ) {
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
					$feedback = $EM_Booking->feedback_message;
				}	
			}
		}

		$return = array('result'=>$result, 'message'=>$feedback, 'error'=>$EM_Booking->get_errors());
		
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

