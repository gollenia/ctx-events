<?php

declare(strict_types=1);

$context = isset($block) && is_object($block) && isset($block->context) && is_array($block->context)
	? $block->context
	: [];
$selected_event = isset($context['ctx-events/eventId']) ? (int) $context['ctx-events/eventId'] : 0;
$fallback_event = get_post_type(get_the_ID()) === 'ctx-event' ? (int) get_the_ID() : 0;
$event_id = $selected_event ?: $fallback_event;

if ($event_id <= 0) {
	return;
}

$image_html = get_the_post_thumbnail(
	$event_id,
	'large',
	[
		'class' => 'wp-image-' . (int) get_post_thumbnail_id($event_id),
	]
);

if ($image_html === '') {
	return;
}
?>

<figure <?php echo get_block_wrapper_attributes(['class' => 'wp-block-image size-large']); ?>>
	<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</figure>
