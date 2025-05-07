<?php

namespace Contexis\Events\Forms;

use Contexis\Events\Collections\TicketCollection;
use Contexis\Events\Forms\Form;
use Contexis\Events\Models\Booking;
use \Contexis\Events\Models\Event;
include('AttendeeForm.php');
class AttendeesForm {
	static $validate;

	static $form;
	static $form_id;
	static $form_name;
	static $form_template;
	
	public static function init(){
		//Exporting
		$instance = new self;
		add_action('em_bookings_table_export_options', array($instance, 'em_bookings_table_export_options')); //show booking form and ticket summary
		
		//Booking interception - will not trigger on multi-booking checkout
		add_filter('em_booking_get_post', array($instance, 'em_booking_get_post'), 2, 2); //get post data + validate
		add_filter('em_booking_validate', array($instance, 'em_booking_validate'), 2, 2); //validate object
		
	}
	
	
	/**
	 * Gets the default form structure for creating a new form
	 * @return array
	 */
	public static function get_form_template(){
	    if( empty(self::$form_template )){
    		self::$form_template = apply_filters('em_attendees_form_get_form_template', array (
				'attendee_name' => array ( 'label' => __('Name','events'), 'type' => 'text', 'fieldid'=>'attendee_name', 'required'=>1 )
    		));	        
	    }
	    return self::$form_template;
	}
	
	/**
	 * Get the EM_Attendee_Form (Extended EM_Form)
	 * @param Event $event
	 * @return EM_Attendee_Form
	 */
	public static function get_form($event = false){
		if( empty(self::$form) || (!empty($event) && (empty(self::$form->event_id) || $event->event_id != self::$form->event_id)) ){

			if(is_numeric($event)){ $event = Event::get_by_id($event); }
			
			self::$form_id = get_post_meta($event->post_id, '_attendee_form', true);

			$form_data = Form::get_form_data(self::$form_id);

			if(empty($form_data)) {
				$form_data = array('form' => self::get_form_template());
				self::$form_name = __('Default','events');
			}

			self::$form_name = get_the_title(self::$form_id);
			self::$form = new AttendeeForm($form_data, 'em_attendee_form', false);
			self::$form->form_required_error = __('Please fill in the field: %s','events');
		}
		return self::$form;
	}

	

	public static function get_attendee_form($event_id){
		$form_id = get_post_meta($event_id, '_attendee_form', true);
		$form_data = Form::get_form_data($form_id, false);
		return $form_data;
	}
	
	/**
	 * Gets the form ID to use from a given Event object or returns the default form id if not defined or no object passed
	 * @param Event $event
	 */
	public static function get_form_id($event = false){
		$custom_form_id = ( !empty($event->post_id) ) ? get_post_meta($event->post_id, '_custom_attendee_form', true):0;
		$form_id = empty($custom_form_id) ? get_option('em_attendee_form_fields') : $custom_form_id;
	    return $form_id;
	}
	
	
	/**
	 * Converts the relevant field names to be relevant for attendees format (i.e. in an array due to unknown number of attendees per booking)
	 * @param EM_Attendee_Form $form
	 * @param Ticket $ticket
	 * @return EM_Attendee_Form
	 */
	public static function get_ticket_form($form, $ticket){
		//modify field ids to contain ticket number and []
		foreach($form->form_fields as $field_id => $form_data){
		    if( $form_data['type'] == 'date' || $form_data['type'] == 'time'){
				$form->form_fields[$field_id]['name'] = "em_attendee_fields[".$ticket->ticket_id."][$field_id][%s][]";
		    }elseif( in_array($form_data['type'], array('radio','checkboxes','multiselect')) ){
			    $form->form_fields[$field_id]['name'] = "em_attendee_fields[".$ticket->ticket_id."][$field_id][%n]";
			}else{
				$form->form_fields[$field_id]['name'] = "em_attendee_fields[".$ticket->ticket_id."][$field_id][]";
		    }
		}
		return $form;
	}
	
	/**
	 * Returns a formatted multi-dimensional associative array of attendee information for a specific booking, split by ticket > attendee > attendee data.
	 * example : array('ticket_id' => array('Attendee 1' => array('Label'=>'Value', 'Label 2'=>'Value 2'), 'Attendee 2' => array(...)...)...);
	 * @param Booking $booking
	 */
	public static function get_booking_attendees($booking): array {
		$attendee_data = [];
		$event = \Contexis\Events\Models\Event::get_by_id($booking->event_id);
		$tickets = TicketCollection::find_by_event_id($event->event_id);
	
		foreach ($tickets as $ticket) {
			$ticket_id = $ticket->ticket_id;
			$attendees = $booking->booking_meta['attendees'][$ticket_id] ?? [];
	
			if (is_array($attendees) && count($attendees) > 0) {
				$attendee_data[$ticket_id] = AttendeesForm::get_ticket_attendees(
					$event->post_id,
					$ticket_id,
					$booking->booking_meta
				);
			} else {
				$attendee_data[$ticket_id] = [];
				for ($i = 1; $i <= $ticket->ticket_spaces; $i++) {
					$key = sprintf(__('Attendee %s', 'events'), $i);
					$attendee_data[$ticket_id][$key] = [];
				}
			}
		}
	
		return $attendee_data;
	}
	

	public static function get_ticket_attendees($event_id, $ticket_id, array $booking_meta, bool $padding = false): array {
		$attendees = [];
		$form = AttendeesForm::get_form($event_id); // liefert Formular-Definition
	
		// Falls Teilnehmerdaten vorhanden sind:
		if (!empty($booking_meta['attendees'][$ticket_id]) && is_array($booking_meta['attendees'][$ticket_id])) {
			$i = 1;
			foreach ($booking_meta['attendees'][$ticket_id] as $field_values) {
				$key = sprintf(__('Attendee %s', 'events'), $i);
				$attendees[$key] = [];
	
				foreach ($form->form_fields as $fieldid => $field) {
					if ($field['type'] === 'html') continue;
	
					$value = $field_values[$fieldid] ?? 'n/a';
					$label = $field['label'] ?? $fieldid;
	
					$attendees[$key][$label] = $form->get_formatted_value($field, $value);
				}
				$i++;
			}
		}
		// Padding aktiv: leere Teilnehmerfelder generieren
		elseif ($padding) {
			$spaces = $booking_meta['booking_spaces'] ?? 1;
	
			for ($i = 1; $i <= $spaces; $i++) {
				$key = sprintf(__('Attendee %s', 'events'), $i);
				$attendees[$key] = [];
	
				foreach ($form->form_fields as $fieldid => $field) {
					if ($field['type'] !== 'html') {
						$attendees[$key][$field['label']] = $form->get_formatted_value($field, 'n/a');
					}
				}
			}
		}
	
		return $attendees;
	}
	
	
	/**
	 * Hooks into em_booking_get_post and validates the 
	 * @param boolean $result
	 * @param Booking $booking
	 * @return bool
	 */
	public static function em_booking_get_post($result, $booking) {
		$EM_Form = self::get_form($booking->event_id);
	
		if (self::$form_id > 0) {
			$booking->booking_meta['attendees'] = [];
			$event = \Contexis\Events\Models\Event::get_by_id($booking->event_id);
			$tickets = $event->get_tickets();
	
			foreach ($tickets as $ticket) {
				for ($i = 0; $i < $ticket->ticket_spaces; $i++) {
					$EM_Form->clear_values(); // wichtig!
					foreach ($EM_Form->fields as &$field) {
						$field['label'] = str_replace('#NUM#', $i + 1, $field['label']);
					}
	
					// get_post: form data holen, aber (noch) nicht validieren
					if ($EM_Form->get_post(false, $ticket->ticket_id, $i)) {
						$values = $EM_Form->get_values();
						$booking->booking_meta['attendees'][$ticket->ticket_id][$i] = $values;
					}
				}
			}
		}
	
		return $result;
	}
	

	public static function em_booking_validate(bool $result, Booking $booking) : bool {
		$EM_Form = self::get_form($booking->event_id);
	
		if (self::$form_id <= 0) {
			return $result;
		}
	
		$event = \Contexis\Events\Models\Event::get_by_id($booking->event_id);
		$tickets = $event->get_tickets();
	
		foreach ($tickets as $ticket) {
			$attendees = $booking->booking_meta['attendees'][$ticket->ticket_id] ?? [];
	
			foreach ($attendees as $i => $attendee_data) {
				$EM_Form->field_values = $attendee_data;
				$EM_Form->errors = [];
	
				foreach ($EM_Form->form_fields as $key => &$field) {
					if (isset($field['label'])) {
						$field['label'] = str_replace('#NUM#', $i + 1, $field['label']);
					}
				}
	
				if (!$EM_Form->validate()) {
					$title = $ticket->ticket_name . " – " . sprintf(__('Attendee %s', 'events'), $i + 1);
					$booking->errors[] = $title . ': ' . implode(', ', $EM_Form->errors);
					$result = false;
				}
			}
		}
	
		return $result;
	}
	
	/*
	 * ----------------------------------------------------------
	 * Booking Table and CSV Export
	 * ----------------------------------------------------------
	 */
	
	/**
	 * Intercepts a CSV export request before the core version hooks in and using similar code generates a breakdown of bookings with all attendees included at the end.
	 * Hooking into the original version of this will cause more looping, which is why we're flat out overriding this here.
	 */
	
	
	public static function em_bookings_table_export_options(){
		?>
		<p><input type="checkbox" name="show_attendees" value="1" /><label><?php _e('Split bookings by attendee','events')?> </label>
		
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('#em-bookings-table-export-form input[name=show_attendees]').click(function(){
					$('#em-bookings-table-export-form input[name=show_tickets]').attr('checked',true);
					//copied from export_overlay_show_tickets function:
					$('#em-bookings-table-export-form .em-bookings-col-item-ticket').show();
					$('#em-bookings-table-export-form #em-bookings-export-cols-active .em-bookings-col-item-ticket input').val(1);
				});
				$('#em-bookings-table-export-form input[name=show_tickets]').change(function(){
					if( !this.checked ){
						$('#em-bookings-table-export-form input[name=show_attendees]').attr('checked',false);
					}
				});
			});
		</script>
		<?php
		
	}


	

	
}
AttendeesForm::init();

?>