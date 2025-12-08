<?php
declare(strict_types=1); 

$id = get_the_ID();
$event = \Contexis\Events\Models\Event::find_by_post(get_post());
if(!$event || !$event->event_rsvp) return;
$spaces = $event->spaces->available();

$icon = $spaces == 0 ? 'sentiment_dissatisfied' : ( $attributes['warningThreshold'] < $spaces ? 'done' : 'report_problem' );

?>

<div class="event-details-item">
	<div class="event-details-image">
		<i class="material-icons material-symbols-outlined"><?php
declare(strict_types=1); echo $icon ?></i>
	</div>
	<div class="event-details-text">
		<h4><?php
declare(strict_types=1); echo $attributes['description'] ?: __("Free spaces", "events") ?></h4>
		<div class="event-details-data">
			<?php
declare(strict_types=1); if ($attributes['showNumber'] && $spaces > 0) : ?>
				<?php
declare(strict_types=1); if ($spaces <= $attributes['warningThreshold']) : ?>
					<div class="event-details-number"><?php
declare(strict_types=1); echo sprintf(_n("Only %s space left", "Only %s spaces left", $spaces, "events"), $spaces) ?></div>
				<?php
declare(strict_types=1); else : ?>
					<div class="event-details-number"><?php
declare(strict_types=1); echo sprintf(_n("%s space left", "%s spaces left", $spaces, "events"), $spaces) ?></div>
				<?php
declare(strict_types=1); endif; ?>
			<?php
declare(strict_types=1); else : ?>
				<?php
declare(strict_types=1); if ($spaces == 0) : ?>
					<div class="event-details-number"><?php
declare(strict_types=1); echo __("No spaces left", "events") ?></div>
				<?php
declare(strict_types=1); elseif ($spaces <= $attributes['warningThreshold']) : ?>
					<div class="event-details-number"><?php
declare(strict_types=1); echo __("Only few spaces left", "events") ?></div>
				<?php
declare(strict_types=1); else : ?>
					<div class="event-details-number"><?php
declare(strict_types=1); echo __("Plenty of spaces left", "events") ?></div>
				<?php
declare(strict_types=1); endif; ?>
			<?php
declare(strict_types=1); endif; ?>
		</div> 
	</div>
</div> 