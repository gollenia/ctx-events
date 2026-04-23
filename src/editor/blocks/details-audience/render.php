<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\BlockEventLoader;
use Contexis\Events\Shared\Infrastructure\Icons\BlockIconRenderer;

$id = get_the_ID();
$audience = get_post_meta($id, '_event_audience', true);

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
		<p class="event-details-data"><?= esc_html($audience) ?></p>
	</div>
</div>
