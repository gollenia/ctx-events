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
if (!$event || !$event->locationDto) {
	return;
}
?>

<p <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo esc_html($event->locationDto->name); ?>
</p>
