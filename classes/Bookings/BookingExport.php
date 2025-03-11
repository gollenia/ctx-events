<?php

use \Contexis\Events\Models\Event;

class BookingExport
{
	
	public static function init() 
	{
		$instance = new self();
		add_action('wp_ajax_em_export_bookings_xls', array($instance, 'export_bookings_xls') );
	}
	
	public function export_bookings_xls()
	{
		if( !isset($_REQUEST['event_id']) ){
			wp_die(__('No event ID provided.','events-manager'));
		}

		$event = Event::find_by_event_id( absint($_REQUEST['event_id']) );

		if( !$event ){
			wp_die(__('Event not found.','events-manager'));
		}
	
		if( isset($_REQUEST['cols']) && is_array($_REQUEST['cols']) ){
			$_REQUEST['cols'] = array_keys(array_filter($_REQUEST['cols']));
		}

		$_REQUEST['limit'] = 0;

		if(isset($_REQUEST['show_attendees']) && $_REQUEST['show_attendees'] == 1) {
			$this->export_attendees_xls($event);
			return;
		}

		
		$EM_Bookings_Table = new EM_Bookings_Table();
	
		$EM_Bookings_Table->limit = 500;
		$EM_Bookings = $EM_Bookings_Table->get_bookings();
		
		$excel_sheet = [$EM_Bookings_Table->get_headers(true)];
		
		while( !empty($EM_Bookings->bookings) ){
			foreach( $EM_Bookings->bookings as $EM_Booking ) { /* @var EM_Booking $EM_Booking */
				//Display all values
				if( $show_tickets ){
					foreach($EM_Booking->get_tickets_bookings()->tickets_bookings as $ticket_booking){ 
						$row = $EM_Bookings_Table->get_row_csv($ticket_booking);
						array_push($excel_sheet, $row);
					}
				}else{
					$row = $EM_Bookings_Table->get_row_csv($EM_Booking);
					array_push($excel_sheet, $row);
				}
			}
			//reiterate loop
			$EM_Bookings_Table->offset += $EM_Bookings_Table->limit;
			$EM_Bookings = $EM_Bookings_Table->get_bookings();
		}
		$xlsx = Shuchkin\SimpleXLSXGen::fromArray( $excel_sheet );
		$xlsx->downloadAs($this->get_file_name($event));
		
		exit();
		
	}

	private function get_file_name(Event | NULL $event = null) : string 
	{
		if($event){
			return $event->event_slug . '-bookings.xlsx';
		}
		return 'bookings.xlsx';
	}

	public function export_attendees_xls($event){
		
		
		$EM_Bookings_Table = new EM_Bookings_Table();
		$alphabet = range('A', 'Z');
		
		$EM_Bookings_Table->limit = 500; 
		$EM_Bookings = $EM_Bookings_Table->get_bookings();
		$form_fields = EM_Attendees_Form::get_form($event->event_id)->form_fields;
		
		$headers = $EM_Bookings_Table->get_headers(true);
		$registration_length = count($headers);
		$titles = array_fill(0, count($headers), '<b><middle><style height="50" bgcolor="#f2f2f2" color="#000000"></style></middle></b>');
		$titles[0] = '<b><middle><style height="25" bgcolor="#f2f2f2" color="#000000">' . __('Registration Fields','events') . '</style></middle></b>';
		foreach($headers as $key => $header){
			$headers[$key] = '<b><middle><style height="50" bgcolor="#f2f2f2" color="#000000">' . $header . '</style></middle></b>';
		}
		
		$i = 0;

		foreach($form_fields as $field ){
			if( $field['type'] == 'html' ) continue;
			$headers[] = EM_Bookings_Table::sanitize_spreadsheet_cell('<b><middle><style height="25" bgcolor="#e2efda" color="#375623">' . $field['label'] . '</style></middle></b>');
			$titles[] = $i == 0 ? '<b><middle><style height="25" bgcolor="#e2efda" color="#375623">' . __('Attendee','events') . '</style></middle></b>' : '<b><middle><style height="25" bgcolor="#e2efda" color="#375623"></style></middle></b>';
			$i++;
		}
		
		$excel_sheet = [$titles];
		$excel_sheet[] = $headers;
		
		while(!empty($EM_Bookings->bookings)){
			foreach( $EM_Bookings->bookings as $EM_Booking ) {
				$attendees_data = EM_Attendees_Form::get_booking_attendees($EM_Booking);
				foreach($EM_Booking->get_tickets_bookings()->tickets_bookings as $ticket_booking){
					$orig_row = $EM_Bookings_Table->get_row_csv($ticket_booking);
					if( !empty($attendees_data[$ticket_booking->ticket_id]) ){ 
						foreach($attendees_data[$ticket_booking->ticket_id] as $attendee_title => $attendee_data){
							$row = $orig_row;
							foreach( $attendee_data as $field_value){
								$row[] = EM_Bookings_Table::sanitize_spreadsheet_cell($field_value);
							}
							array_push($excel_sheet, $row);
						}
					}
				}
			}
			//reiterate loop
			$EM_Bookings_Table->offset += $EM_Bookings_Table->limit;
			$EM_Bookings = $EM_Bookings_Table->get_bookings();
		}
		$xlsx = Shuchkin\SimpleXLSXGen::fromArray( $excel_sheet );
		$xlsx->mergeCells('A1:'. $alphabet[$registration_length-1].'1');
		$xlsx->mergeCells($alphabet[$registration_length].'1:'. $alphabet[count($headers)-1].'1');
		$xlsx->downloadAs($this->get_file_name($event));
		exit();
		
	}
}

BookingExport::init();