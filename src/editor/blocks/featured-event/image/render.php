<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\Bindings\EventBindingContext;

$context = isset($block) && is_object($block) && isset($block->context) && is_array($block->context)
	? $block->context
	: [];
$event_id = EventBindingContext::resolveEventIdFromContext($context);

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
