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
		<?= BlockEventLoader::renderIcon($attributes['icon'] ?: 'date') ?>
	</div>
	<div class="event-details-text">
		<h4><?= esc_html($attributes['description'] ?: __('Date', 'ctx-events')) ?></h4>
		<p><?= esc_html($date) ?></p>
	</div>
	<?php if ($ical) : ?>
		<p class="event-details-action">
			<a href="<?= esc_url($ical) ?>" target="_blank">
				<?= BlockEventLoader::renderIcon('download') ?>
			</a>
		</p>
	<?php endif; ?>
</div>
