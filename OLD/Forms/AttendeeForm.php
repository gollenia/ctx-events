<?php
namespace Contexis\Events\Forms;
class AttendeeForm extends Form {
	public $event_id;
	
    function get_post( $validate = false, $ticket_id = 0, $attendee_index = 0 ){
        $this->field_values = [];
    	foreach($this->form_fields as $field){
    		$fieldid = $field['fieldid'];
			if( !isset($_REQUEST['em_attendee_fields'][$ticket_id][$attendee_index][$fieldid]) || $_REQUEST['em_attendee_fields'][$ticket_id][$attendee_index][$fieldid] == '' ) continue;
    		
			if( !is_array($_REQUEST['em_attendee_fields'][$ticket_id][$attendee_index][$fieldid])){
				$this->field_values[$fieldid] = wp_kses_data(stripslashes($_REQUEST['em_attendee_fields'][$ticket_id][$attendee_index][$fieldid]));
				continue;
			}
			
			if( is_array($_REQUEST['em_attendee_fields'][$ticket_id][$attendee_index][$fieldid])){
				$array = array();
				foreach( $_REQUEST['em_attendee_fields'][$ticket_id][$attendee_index][$fieldid] as $key => $array_value ){
					$array[$key] = wp_kses_data(stripslashes($array_value));
				}
				$this->field_values[$fieldid] = $array;
			}
    	}
    	if( $validate ){
    		return $this->validate();
    	}
    	return true;
    }
    
    
}