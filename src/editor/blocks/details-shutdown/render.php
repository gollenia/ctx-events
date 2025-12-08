<?php
declare(strict_types=1); 

$id = get_the_ID();
$event = \Contexis\Events\Models\Event::find_by_post(get_post());
if(!$event || !$event->event_rsvp) return;

$date_start = $event->get_rsvp_start()->getTimestamp();
$date_end = $event->get_rsvp_end()->getTimestamp();

$current_time = time();

if ($current_time < $date_start) {
    // Booking has not started yet
    $description = __("Booking will start on", "events");
    $date = \Contexis\Events\Intl\Date::get_date($event->get_rsvp_start()->getTimestamp());
} elseif ($current_time > $date_end) {
    // Booking has ended
    $description = __("Booking has ended on", "events");
    $date = \Contexis\Events\Intl\Date::get_date($event->get_rsvp_end()->getTimestamp());
} else {
    // Booking is ongoing
    $description = __("Booking ends on", "events");
    $date = \Contexis\Events\Intl\Date::get_date($event->get_rsvp_end()->getTimestamp());
}



?>

<div class="event-details-item">
		<div class="event-details-image">
			<i class="event-details-icon material-icons material-symbols-outlined">event_busy</i>
		</div>
		<div class="event-details-text">
			<h4><?php
declare(strict_types=1); echo $description ?></h4>
			<time class="event-details-data">
				<?php
declare(strict_types=1); echo $date ?>
			</time>
		</div>                        
	</div>