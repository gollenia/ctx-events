<?php

use Contexis\Events\Event\Infrastructure\BlockEventLoader;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Platform\Bootstrap;
use Contexis\Events\Shared\Infrastructure\Icons\BlockIconRenderer;

$post_id = get_the_ID();

$repository = Bootstrap::container()->get(EventRepository::class);
$event = $repository->find(EventId::from($post_id));

if (!$event) {
    return;
}

if (!$event->acceptsBookings()) {
    return;
}

$button_title = isset($attributes['buttonTitle']) && is_string($attributes['buttonTitle'])
	? $attributes['buttonTitle']
	: '';
$button_icon = isset($attributes['buttonIcon']) && is_string($attributes['buttonIcon'])
	? $attributes['buttonIcon']
	: '';
$icon_only = filter_var(
	$attributes['iconOnly'] ?? false,
	FILTER_VALIDATE_BOOLEAN,
);
$icon_right = filter_var(
	$attributes['iconRight'] ?? false,
	FILTER_VALIDATE_BOOLEAN,
);

$classNames = [
    'ctx__button',
    $icon_only ? 'ctx__button--icon-only' : '',
    $icon_right ? 'ctx__button--reverse' : '',
];

$block_attributes = get_block_wrapper_attributes([
	'class' => implode(' ', array_filter($classNames)),
	'data-ctx-booking-trigger' => 'true',
	'data-ctx-event-id' => (string) $post_id,
]);

?>

<button <?php echo $block_attributes; ?> type="button">
<?php
if ($button_icon !== '') {
    echo BlockIconRenderer::render($button_icon);
}

if (!$icon_only) {
    echo esc_html($button_title ?: __('Register', 'ctx-events'));
}
?>
</button>

<?php
static $booking_app_rendered = false;

if (!$booking_app_rendered) {
	$booking_app_rendered = true;

	add_action('wp_footer', static function () use ($post_id): void {
		echo '<div id="booking_app"></div>';
	});
}
