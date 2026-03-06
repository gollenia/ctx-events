<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\BlockEventLoader;

$id = get_the_ID();
$audience = get_post_meta($id, '_event_audience', true);

if (!$audience) {
    return;
}

?>

<div class="event-details-item">
	<div class="event-details-image">
		<?= BlockEventLoader::renderIcon($attributes['icon'] ?: 'male') ?>
	</div>
	<div class="event-details-text">
		<h4><?= esc_html($attributes['description'] ?: __('Audience', 'ctx-events')) ?></h4>
		<div class="event-details-data"><?= esc_html($audience) ?></div>
	</div>
</div>
