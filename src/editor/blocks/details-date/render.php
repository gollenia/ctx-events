<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\BlockEventLoader;

$event = BlockEventLoader::load(get_the_ID());
if (!$event) {
    return;
}

$date = BlockEventLoader::formatDateRange($event->startDate, $event->endDate);
$ical = $attributes['iCalLink'] ?? false;

?>

<div class="event-details-item">
	<div class="event-details-image">
		<?= BlockEventLoader::renderIcon($attributes['icon'] ?: 'calendar_today') ?>
	</div>
	<div class="event-details-text">
		<h4><?= esc_html($attributes['description'] ?: __('Date', 'ctx-events')) ?></h4>
		<div class="event-details-data"><?= esc_html($date) ?></div>
	</div>
	<?php if ($ical) : ?>
		<div class="event-details-action">
			<a href="<?= esc_url($ical) ?>" target="_blank">
				<?= BlockEventLoader::renderIcon('download') ?>
			</a>
		</div>
	<?php endif; ?>
</div>
