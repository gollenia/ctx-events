<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\Bindings\EventBindingContext;
use Contexis\Events\Event\Infrastructure\BlockEventLoader;

$context = isset($block) && is_object($block) && isset($block->context) && is_array($block->context)
	? $block->context
	: [];
$event_id = EventBindingContext::resolveEventIdFromContext($context);

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

$level = isset($attributes['level']) ? max(1, min(6, (int) $attributes['level'])) : 2;
$tag = 'h' . $level;
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'wp-block-heading']);
?>

<<?php echo esc_html($tag); ?> <?php echo $wrapper_attributes; ?>>
	<a href="<?php echo esc_url($permalink); ?>">
		<?php echo esc_html($event->name); ?>
	</a>
</<?php echo esc_html($tag); ?>>
