<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\BlockEventLoader;
use Contexis\Events\Shared\Infrastructure\Icons\BlockIconRenderer;

$event = BlockEventLoader::load(get_the_ID());
if (!$event) {
    return;
}

$time = BlockEventLoader::formatTimeRange($event->startDate, $event->endDate);

?>

<div class="event-details-item">
	<div class="event-details-image">
		<?= BlockIconRenderer::render($attributes['icon'] ?? 'time') ?>
	</div>
	<div class="event-details-text">
		<h4 class="event-details-title"><?= esc_html($attributes['description'] ?: __('Time', 'ctx-events')) ?></h4>
		<div class="event-details-data"><?= esc_html($time) ?></div>
	</div>
</div>
