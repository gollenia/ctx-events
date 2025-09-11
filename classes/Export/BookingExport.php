<?php

namespace Contexis\Events\Export;

use Contexis\Events\Admin\BookingsTable;
use Contexis\Events\Forms\AttendeesForm;
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

		$event = Event::get_by_id( absint($_REQUEST['event_id']) );

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

		$show_tickets = isset($_REQUEST['show_tickets']) && $_REQUEST['show_tickets'] == 1 ? true : false;
		$bookings_table = new BookingsTable();
		$bookings_table->limit = 500;
		$bookings_table->offset = 0;

		$excel_sheet = [$bookings_table->get_headers(true)];

		do {
			$booking_collection = $bookings_table->get_bookings();

			foreach ( $booking_collection as $booking ) {
				$attendees = $booking->attendees ?? [];

				if ( $show_tickets ) {
					foreach ( $attendees as $ticket_id => $attendee_group ) {
						$ticket = \Contexis\Events\Models\Ticket::get_by_id($booking->event_id, $ticket_id);
						if( !$ticket ) continue;

						$row = $bookings_table->get_row_csv( $booking, $ticket, count($attendee_group) );
						$excel_sheet[] = $row;
					}
				} else {
					$row = $bookings_table->get_row_csv( $booking );
					$excel_sheet[] = $row;
				}
			}

			$bookings_table->offset += $bookings_table->limit;
		} while ( !empty($booking_collection->bookings) );
		$xlsx = \Shuchkin\SimpleXLSXGen::fromArray( $excel_sheet );
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

	public function export_attendees_xls($event) {
		$bookings_table = new BookingsTable();
		$alphabet = range('A', 'Z');
	
		$bookings_table->limit = 500;
		$booking_collection = $bookings_table->get_bookings();
		$form_fields = AttendeesForm::get_form($event->event_id)->form_fields;
	
		// Headline Row
		$headers = $bookings_table->get_headers(true);
		$registration_length = count($headers);
		$titles = array_fill(0, $registration_length, '<b><middle><style height="50" bgcolor="#f2f2f2" color="#000000"></style></middle></b>');
		$titles[0] = '<b><middle><style height="25" bgcolor="#f2f2f2" color="#000000">' . __('Registration Fields','events') . '</style></middle></b>';
	
		foreach ($headers as $key => $header) {
			$headers[$key] = '<b><middle><style height="50" bgcolor="#f2f2f2" color="#000000">' . $header . '</style></middle></b>';
		}
	
		$i = 0;
		foreach ($form_fields as $field) {
			if ($field['type'] === 'html') continue;
			$headers[] = BookingsTable::sanitize_spreadsheet_cell('<b><middle><style height="25" bgcolor="#e2efda" color="#375623">' . $field['label'] . '</style></middle></b>');
			$titles[] = $i === 0
				? '<b><middle><style height="25" bgcolor="#e2efda" color="#375623">' . __('Attendee','events') . '</style></middle></b>'
				: '<b><middle><style height="25" bgcolor="#e2efda" color="#375623"></style></middle></b>';
			$i++;
		}
	
		$excel_sheet = [$titles, $headers];
	
		while (!empty($booking_collection->bookings)) {
			foreach ($booking_collection->bookings as $booking) {
				$row_base = $bookings_table->get_row_csv($booking);
				$attendees_data = AttendeesForm::get_booking_attendees($booking); 
	
				foreach ($attendees_data as $ticket_id => $attendees) {
					foreach ($attendees as $attendee_title => $fields) {
						$row = $row_base;
						foreach ($fields as $value) {
							$row[] = BookingsTable::sanitize_spreadsheet_cell($value);
						}
						$excel_sheet[] = $row;
					}
				}
			}
			$bookings_table->offset += $bookings_table->limit;
		}
	
		$xlsx = \Shuchkin\SimpleXLSXGen::fromArray($excel_sheet);
		$xlsx->mergeCells('A1:' . $alphabet[$registration_length - 1] . '1');
		$xlsx->mergeCells($alphabet[$registration_length] . '1:' . $alphabet[count($headers) - 1] . '1');
		$xlsx->downloadAs($this->get_file_name($event));
		exit;
	}
}

BookingExport::init();