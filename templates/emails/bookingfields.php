<table >
<?php

$form_fields = EM_Booking_Form::get_form($booking->event_id)->form_fields;
$form_values = $booking->booking_meta['booking'] ? array_merge($booking->booking_meta['registration'], $booking->booking_meta['booking']) : $booking->booking_meta['registration'];

foreach($form_fields as $name => $field) {
	if($field['type'] == "html") {
		continue;
	}
	$value = $form_values[$name];
	
	if($field['type'] == "email") {
		$value = "<a href='mailto:$value'>$value</a>";
	}
	if($field['type'] == "checkbox") {
		$value = $value ? __("Yes", "events") : __("No", "events");
	}
	if($field['type'] == "date") {
		$value = date_i18n(get_option('date_format'), strtotime($value));
	}
	echo "<tr>";
	if($name == "info") continue;
	echo "<td><b>" . ($field['label'] ?: $name) . "</b></td>";
	echo "<td>" . $value . "</td>";
	echo "</tr>";
}
?>
</table>