<?php
declare(strict_types=1); 

global $post;
$event = \Contexis\Events\Models\Event::find_by_post($post);
if(!$event) return;

if($attributes['overwritePrice']) {
	
}
$price = $event->get_formatted_price();
$is_free = $event->is_free();

?>

<?php
declare(strict_types=1); if($price->free && !$attributes['overwritePrice']) : ?>
	<div class="event-details-item">
		<div class="event-details-image">
			<i class="material-icons material-symbols-outlined">savings</i>
		</div>
		<div class="event-details-text">
			<h4><?php
declare(strict_types=1); echo $attributes['description'] ? $attributes['description'] : __("Price", "events") ?></h4>
			<div class="event-details-data"><?php
declare(strict_types=1); echo __("Free", "events") ?></div> 
		</div>
	</div>
<?php
declare(strict_types=1); else : ?>
	<div class="event-details-item">
		<div class="event-details-image">
			<i class="event-details-icon material-icons material-symbols-outlined">payments</i>
		</div>
		<div class="event-details-text">
			<h4><?php
declare(strict_types=1); echo $attributes['description'] ? $attributes['description'] : __("Price", "events") ?></h4>
			<div class="event-details-data"><?php
declare(strict_types=1); echo $attributes['overwritePrice'] ? $attributes['overwritePrice'] : $price->format ?></div> 
		</div>
	</div>
<?php
declare(strict_types=1); endif; ?>

