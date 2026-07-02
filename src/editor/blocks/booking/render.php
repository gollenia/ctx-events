<?php

use Contexis\Events\Event\Infrastructure\BlockEventLoader;
use Contexis\Events\Event\Domain\Enums\BookingDenyReason;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Platform\Bootstrap;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Infrastructure\Icons\BlockIconRenderer;

$post_id = get_the_ID();

$repository = Bootstrap::container()->get(EventRepository::class);
$booking_repository = Bootstrap::container()->get(BookingRepository::class);
$clock = Bootstrap::container()->get(Clock::class);
$event = $repository->find(EventId::from($post_id));

if (!$event) {
    return;
}

if (!$event->acceptsBookings()) {
    return;
}

$booking_decision = $event->canBookAt(
	$clock->now(),
	$booking_repository->getTicketBookingsForEvent(EventId::from($post_id)),
);
$bookable_tickets = $event->tickets?->getBookableTickets(
	$booking_repository->getTicketBookingsForEvent(EventId::from($post_id)),
	$clock->now(),
);
$is_booking_disabled = !$booking_decision->allowed || $bookable_tickets?->count() === 0;

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
    $is_booking_disabled ? 'ctx__button--disabled' : '',
];

$block_attributes = get_block_wrapper_attributes([
	'class' => implode(' ', array_filter($classNames)),
	'data-ctx-booking-trigger' => 'true',
	'data-ctx-event-id' => (string) $post_id,
]);

$disabled_label = match ($booking_decision->reason) {
	BookingDenyReason::ENDED => __('Booking ended', 'ctx-events'),
	BookingDenyReason::NOT_STARTED => __('Booking not started yet', 'ctx-events'),
	BookingDenyReason::SOLD_OUT,
	BookingDenyReason::NO_CAPACITY => __('Sold out', 'ctx-events'),
	BookingDenyReason::NO_TICKETS => __('No tickets available', 'ctx-events'),
	BookingDenyReason::DISABLED => __('Booking disabled', 'ctx-events'),
	default => $bookable_tickets?->count() === 0
		? __('No tickets available', 'ctx-events')
		: __('Not bookable', 'ctx-events'),
};

$button_label = $is_booking_disabled
	? $disabled_label
	: ($button_title ?: __('Register', 'ctx-events'));

?>

<button
	<?php echo $block_attributes; ?>
	type="button"
	<?php disabled($is_booking_disabled); ?>
	<?php if ($is_booking_disabled) : ?>
		title="<?php echo esc_attr($disabled_label); ?>"
		aria-disabled="true"
	<?php endif; ?>
>
<?php
if ($button_icon !== '') {
    echo BlockIconRenderer::render($button_icon);
}

if (!$icon_only) {
    echo esc_html($button_label);
}
?>
</button>

<?php
static $booking_app_rendered = false;

if (!$booking_app_rendered) {
	$booking_app_rendered = true;

	add_action('wp_footer', static function () use ($post_id): void {
		echo '<div id="booking_app" data-ctx-event-id="' . esc_attr((string) $post_id) . '"></div>';
	});
}
