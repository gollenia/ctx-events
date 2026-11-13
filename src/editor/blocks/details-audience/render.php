<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\BlockEventLoader;
use Contexis\Events\Shared\Infrastructure\Icons\BlockIconRenderer;

$id = get_the_ID();
$audience = get_post_meta($id, '_event_audience', true);

if (!$audience) {
    $minimumAge = (int) get_post_meta($id, '_minimum_age', true);
    $maximumAge = (int) get_post_meta($id, '_maximum_age', true);

    $audience = match (true) {
        $minimumAge > 0 && $maximumAge > 0 => sprintf(__('%1$d–%2$d years', 'ctx-events'), $minimumAge, $maximumAge),
        $minimumAge > 0 => sprintf(__('from %d years', 'ctx-events'), $minimumAge),
        $maximumAge > 0 => sprintf(__('up to %d years', 'ctx-events'), $maximumAge),
        default => '',
    };
}

if (!$audience) {
    return;
}

?>

<div class="event-details-item">
	<div class="event-details-image">
		<?= BlockIconRenderer::render($attributes['icon'] ?: 'audience') ?>
	</div>
	<div class="event-details-text">
		<h4 class="event-details-title"><?= esc_html($attributes['description'] ?: __('Audience', 'ctx-events')) ?></h4>
		<div class="event-details-data"><?= esc_html($audience) ?></div>
	</div>
</div>
