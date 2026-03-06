<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\BlockEventLoader;

$event = BlockEventLoader::load(get_the_ID());
if (!$event) {
    return;
}

$time = BlockEventLoader::formatTimeRange($event->startDate, $event->endDate);

?>

<div class="event-details-item">
	<div class="event-details-image">
		<?= BlockEventLoader::renderIcon($attributes['icon'] ?? 'schedule') ?>
	</div>
	<div class="event-details-text">
		<h4><?= esc_html($attributes['description'] ?: __('Time', 'ctx-events')) ?></h4>
		<div class="description-data"><?= esc_html($time) ?></div>
	</div>
</div>
