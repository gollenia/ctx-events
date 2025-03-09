<?php 

$id = get_the_ID();
$event = EM_Event::find_by_post_id($id);
if(!$event) return;
$date = \Contexis\Events\Intl\Date::get_date($event->start()->getTimestamp(), $event->end()->getTimestamp());
$ical = $attributes['iCalLink'] ?: false;
?>

<div class="event-details-item">
	<div class="event-details-image">
		<i class="material-icons material-symbols-outlined"><?php echo $attributes['icon'] ?: 'event' ?></i>
	</div>
	<div class="event-details-text">
		<h4><?php echo $attributes['description'] ?: __("Date", "events") ?></h4>
		<div class="event-details-data"><?php echo $date ?></div> 
	</div>
	<?php if($ical) { ?>
		<div class="event-details-action">
		<a href="<?php echo $ical ?>" target="_blank">
				<i class="material-icons material-symbols-outlined">calendar_today</i>
		</a>
		</div>
	<?php } ?>
</div> 