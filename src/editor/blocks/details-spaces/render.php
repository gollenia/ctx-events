<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\BlockEventLoader;

$event = BlockEventLoader::load(get_the_ID());
if (!$event || !$event->bookingSummary) {
    return;
}

$summary = $event->bookingSummary;
if ($summary->available === null) {
    return;
}

$spaces = $summary->available;
$warningThreshold = $attributes['warningThreshold'] ?? 5;
$icon = $spaces === 0 ? 'sentiment_dissatisfied' : ($warningThreshold < $spaces ? 'done' : 'report_problem');

?>

<div class="event-details-item">
	<div class="event-details-image">
		<?= BlockEventLoader::renderIcon($icon) ?>
	</div>
	<div class="event-details-text">
		<h4><?= esc_html($attributes['description'] ?? __('Free spaces', 'ctx-events')) ?></h4>
		<div class="event-details-data">
			<?php if (($attributes['showNumber'] ?? false) && $spaces > 0) : ?>
				<?php if ($spaces <= $warningThreshold) : ?>
					<div class="event-details-number"><?= esc_html(sprintf(_n('Only %s space left', 'Only %s spaces left', $spaces, 'ctx-events'), $spaces)) ?></div>
				<?php else : ?>
					<div class="event-details-number"><?= esc_html(sprintf(_n('%s space left', '%s spaces left', $spaces, 'ctx-events'), $spaces)) ?></div>
				<?php endif; ?>
			<?php else : ?>
				<?php if ($spaces === 0) : ?>
					<div class="event-details-number"><?= esc_html(__('No spaces left', 'ctx-events')) ?></div>
				<?php elseif ($spaces <= $warningThreshold) : ?>
					<div class="event-details-number"><?= esc_html(__('Only few spaces left', 'ctx-events')) ?></div>
				<?php else : ?>
					<div class="event-details-number"><?= esc_html(__('Plenty of spaces left', 'ctx-events')) ?></div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
