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

$post = get_post($event_id);
if (!$post instanceof WP_Post || $post->post_type !== 'ctx-event') {
	return;
}

$words = isset($attributes['words']) ? max(1, (int) $attributes['words']) : 26;
$excerpt = has_excerpt($event_id)
	? get_the_excerpt($event_id)
	: wp_trim_words(wp_strip_all_tags((string) $post->post_content), $words);

if ($excerpt === '') {
	return;
}
?>

<p <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo esc_html($excerpt); ?>
</p>
