<?php 

$id = get_the_ID();
$event = EM_Event::find_by_post(get_post());
if(!$event->event_rsvp) return;

$date_start = $event->rsvp_start()->getTimestamp();
$date_end = $event->rsvp_end()->getTimestamp();

$current_time = time();

if ($current_time < $date_start) {
    // Booking has not started yet
    $description = __("Booking will start on", "events");
    $date = \Contexis\Events\Intl\Date::get_date($event->rsvp_start()->getTimestamp());
} elseif ($current_time > $date_end) {
    // Booking has ended
    $description = __("Booking has ended on", "events");
    $date = \Contexis\Events\Intl\Date::get_date($event->rsvp_end()->getTimestamp());
} else {
    // Booking is ongoing
    $description = __("Booking ends on", "events");
    $date = \Contexis\Events\Intl\Date::get_date($event->rsvp_end()->getTimestamp());
}



?>

<div class="event-details-item">
		<div class="event-details-image">
			<i class="event-details-icon material-icons material-symbols-outlined">event_busy</i>
		</div>
		<div class="event-details-text">
			<h4><?php echo $description ?></h4>
			<time class="event-details-data">
				<?php echo $date ?>
			</time>
		</div>                        
	</div>