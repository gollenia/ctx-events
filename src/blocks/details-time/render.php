<?php 

$event = EM_Event::find_by_post(get_post());
$time = \Contexis\Events\Intl\Date::get_time($event->start()->getTimestamp(), $event->end()->getTimestamp());

?>

<div class="event-details-item">
	<div class="event-details-image">
		<i class="event-details-icon material-icons material-symbols-outlined"><?php echo $attributes['icon'] ? $attributes['icon'] : 'schedule' ?></i>
	</div>
	<div class="event-details-text">
		<h4><?php echo $attributes['description'] ?: __("Time", "events") ?></h4>
		<div class="description-data"><?php echo $time ?></div> 
	</div>
</div> 