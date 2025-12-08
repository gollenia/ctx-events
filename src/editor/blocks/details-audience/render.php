<?php
declare(strict_types=1);
$id = get_the_ID();

$audience = get_post_meta($id, '_event_audience', true);

if(!$audience) return;
?>


<div class="event-details-item">
	<div class="event-details-image">
		<i class="material-icons material-symbols-outlined"><?php
declare(strict_types=1); echo $attributes['icon'] ?: 'male' ?></i>
	</div>
	<div class="event-details-text">
	<h4><?php
declare(strict_types=1); echo $attributes['description'] ?: __("Audience", "events") ?></h4>
		<div class="event-details-data"><?php
declare(strict_types=1); echo get_post_meta($id, '_event_audience', true) ?></div>
	</div>
</div> 