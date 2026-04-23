<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\BlockEventLoader;
use Contexis\Events\Shared\Infrastructure\Icons\BlockIconRenderer;

$event = BlockEventLoader::load(get_the_ID());
if (!$event || !$event->bookingSummary) {
    return;
}

$summary = $event->bookingSummary;
if (!$summary->bookingStart && !$summary->bookingEnd) {
    return;
}

$now = time();
$dateFormat = get_option('date_format');

if ($summary->bookingStart && $now < $summary->bookingStart->getTimestamp()) {
    $description = __('Booking will start on', 'ctx-events');
    $date = wp_date($dateFormat, $summary->bookingStart->getTimestamp());
} elseif ($summary->bookingEnd && $now > $summary->bookingEnd->getTimestamp()) {
    $description = __('Booking has ended on', 'ctx-events');
    $date = wp_date($dateFormat, $summary->bookingEnd->getTimestamp());
} else {
    $description = __('Booking ends on', 'ctx-events');
    $date = wp_date($dateFormat, $summary->bookingEnd->getTimestamp());
}

?>

<div class="event-details-item">
	<div class="event-details-image">
		<?= BlockIconRenderer::render('booking_closed') ?>
	</div>
	<div class="event-details-text">
		<h4 class="event-details-title"><?= esc_html($description) ?></h4>
		<time class="event-details-data"><?= esc_html($date) ?></time>
	</div>
</div>
