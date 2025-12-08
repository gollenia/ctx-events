<?php
declare(strict_types=1);
global $post;
$event = \Contexis\Events\Models\Event::find_by_post($post);

if(!$event) return;
$location = $event->get_location();
if(empty($location->post_id)) return;
$block_attributes = get_block_wrapper_attributes();

$has_photo = strpos($block_attributes, 'is-style-photo') !== false;
$photo = get_the_post_thumbnail_url( $location->post_id, "post-thumbnail" );
?>

<div class="event-details-item">
	<div class="event-details-image">
		<?php
declare(strict_types=1); if ($has_photo && $photo) : ?>
			
			<img class="event-details-image" src="<?php
declare(strict_types=1); echo $photo ?>" alt="<?php
declare(strict_types=1); echo $speaker->name ?>">
		<?php
declare(strict_types=1); else: ?>
			<i class="material-icons material-symbols-outlined"><?php
declare(strict_types=1); echo $attributes['icon'] ?: 'place' ?></i>
		<?php
declare(strict_types=1); endif; ?>
	</div>
	<div class="event-details-text">
		<h4><?php
declare(strict_types=1); echo $attributes['description'] ?: __("Location", "events") ?></h4>
		
		<address class="event-details-data">
			<?php
declare(strict_types=1); if($attributes['showTitle']) echo $location->location_name . '<br />' ?>
			<?php
declare(strict_types=1); if($attributes['showAddress']) echo $location->location_address . '<br />' ?>
			<?php
declare(strict_types=1); if($attributes['showZip']) echo $location->location_postcode ?> <?php
declare(strict_types=1); if($attributes['showCity']) echo $location->location_town  . '<br />' ?>
			<?php
declare(strict_types=1); if($attributes['showCountry']) echo $location->location_country ?>
		</address>
		
	</div>

	<?php
declare(strict_types=1); if($attributes['showLink'] ): ?>
		<div class="event-details-action">
			<a target="_blank" href="<?php
declare(strict_types=1); echo $attributes['url'] ?: $location->location_url ?>"><i class="material-icons material-symbols-outlined">navigation</i></a>
		</div>
	<?php
declare(strict_types=1); endif; ?>
</div> 
