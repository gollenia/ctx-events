<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\BlockEventLoader;

$context = isset($block) && is_object($block) && isset($block->context) && is_array($block->context)
	? $block->context
	: [];
$selected_event = isset($context['ctx-events/eventId']) ? (int) $context['ctx-events/eventId'] : 0;
$fallback_event = get_post_type(get_the_ID()) === 'ctx-event' ? (int) get_the_ID() : 0;
$event_id = $selected_event ?: $fallback_event;

if ($event_id <= 0) {
	return;
}

$event = BlockEventLoader::load($event_id);
if (!$event) {
	return;
}

$permalink = get_permalink($event_id);
if (!$permalink) {
	return;
}

$label = isset($attributes['text']) && is_string($attributes['text']) && $attributes['text'] !== ''
	? $attributes['text']
	: __('View event', 'ctx-events');
?>

<div <?php echo get_block_wrapper_attributes(['class' => 'wp-block-button']); ?>>
	<a class="wp-block-button__link wp-element-button" href="<?php echo esc_url($permalink); ?>">
		<?php echo esc_html($label); ?>
	</a>
</div>
