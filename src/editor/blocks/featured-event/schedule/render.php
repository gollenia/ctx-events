<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\Bindings\EventBindingContext;
use Contexis\Events\Event\Infrastructure\BlockEventLoader;

if (!function_exists('ctx_events_featured_schedule_countdown')) {
	function ctx_events_featured_schedule_countdown(\DateTimeImmutable $start): string
	{
		$now = new \DateTimeImmutable('now', wp_timezone());
		$remaining = $start->getTimestamp() - $now->getTimestamp();

		if ($remaining <= 0) {
			return __('Started', 'ctx-events');
		}

		return sprintf(
			/* translators: %s: remaining time until the event starts */
			__('in %s', 'ctx-events'),
			human_time_diff($now->getTimestamp(), $start->getTimestamp())
		);
	}
}

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

$date = BlockEventLoader::formatDateRange($event->startDate, $event->endDate);
$time = BlockEventLoader::formatTimeRange($event->startDate, $event->endDate);
$display_mode = isset($attributes['displayMode']) && is_string($attributes['displayMode'])
	? $attributes['displayMode']
	: 'date-time';
$value = match ($display_mode) {
	'date' => $date,
	'time' => $time,
	'countdown' => ctx_events_featured_schedule_countdown($event->startDate),
	default => implode(', ', array_filter([$date, $time])),
};

if ($value === '') {
	return;
}

$wrapper_attributes = $display_mode === 'countdown'
	? get_block_wrapper_attributes([
		'data-ctx-featured-countdown' => 'true',
		'data-ctx-featured-countdown-target' => $event->startDate->format(DATE_ATOM),
	])
	: get_block_wrapper_attributes();
?>

<p <?php echo $wrapper_attributes; ?>>
	<?php echo esc_html($value); ?>
</p>
