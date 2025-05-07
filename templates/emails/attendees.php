<?php

use Contexis\Events\Forms\AttendeesForm;
use Contexis\Events\Models\Ticket;

$tickets = $booking->booking_meta['attendees'];
$ticket_array = [];
$form_fields = AttendeesForm::get_form($booking->event_id)->form_fields;

$data = $booking->get_attendees();

?>

<table>
	<tr>
		<?php 
			echo "<th>" . "Ticket" . "</th>";
			foreach($form_fields as $name => $field) {
				if($name == "info") continue;
				echo "<th>" . $field['label'] . "</th>";
			}
			echo "<th>" . "Price" . "</th>";
		?>
	</tr><?php
	foreach($data as $ticket) {

		$ticket_data = Ticket::get_by_id($booking->event_id, $ticket["ticket_id"]);
		echo "<tr>" ;
		echo "<td>" . $ticket_data->ticket_name . "</td>";
		
		
		foreach($form_fields as $name => $field) {
			if($name == "info") continue;
			if(!array_key_exists($name, $ticket['fields'])) {
				echo "<td></td>";
				continue;
			}
			echo "<td>" . $ticket['fields'][$name] . "</td>";
		}
		echo "<td>" . number_format((float)$ticket_data->ticket_price, 2, ',', '.') . "</td>";
		echo "</tr>";
	}
	?>
</table>