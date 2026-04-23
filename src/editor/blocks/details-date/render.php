<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\BlockEventLoader;
use Contexis\Events\Shared\Infrastructure\Icons\BlockIconRenderer;

$event = BlockEventLoader::load(get_the_ID());
if (!$event) {
    return;
}

$date = BlockEventLoader::formatDateRange($event->startDate, $event->endDate);
$ical = $attributes['iCalLink'] ?? false;

?>

<div class="event-details-item">
	<div class="event-details-image">
		<?= BlockIconRenderer::render($attributes['icon'] ?: 'date') ?>
	</div>
	<div class="event-details-text">
		<h4 class="event-details-title"><?= esc_html($attributes['description'] ?: __('Date', 'ctx-events')) ?></h4>
		<p class="event-details-data"><?= esc_html($date) ?></p>
	</div>
	<?php if ($ical) : ?>
		<p class="event-details-action">
			<a href="<?= esc_url($ical) ?>" target="_blank">
				<?= BlockIconRenderer::render('download') ?>
			</a>
		</p>
	<?php endif; ?>
</div>
