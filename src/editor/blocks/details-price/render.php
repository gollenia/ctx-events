<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\BlockEventLoader;

$event = BlockEventLoader::load(get_the_ID());
if (!$event || !$event->bookingSummary) {
    return;
}

$summary = $event->bookingSummary;
$overwritePrice = $attributes['overwritePrice'] ?? '';
$lowestPrice = $summary->lowestPrice;
$highestPrice = $summary->highestPrice;

$isFree = !$overwritePrice && $lowestPrice !== null && $lowestPrice->isFree() && $highestPrice->isFree();

if (!$isFree && $lowestPrice !== null) {
    $priceDisplay = $lowestPrice->equals($highestPrice)
        ? BlockEventLoader::formatPrice($lowestPrice)
        : BlockEventLoader::formatPrice($lowestPrice) . ' – ' . BlockEventLoader::formatPrice($highestPrice);
}

?>

<?php if ($isFree) : ?>
	<div class="event-details-item">
		<div class="event-details-image">
			<?= BlockEventLoader::renderIcon('price') ?>
		</div>
		<div class="event-details-text">
			<h4><?= esc_html($attributes['description'] ?: __('Price', 'ctx-events')) ?></h4>
			<div class="event-details-data"><?= esc_html(__('Free', 'ctx-events')) ?></div>
		</div>
	</div>
<?php elseif ($overwritePrice || isset($priceDisplay)) : ?>
	<div class="event-details-item">
		<div class="event-details-image">
			<?= BlockEventLoader::renderIcon('payment') ?>
		</div>
		<div class="event-details-text">
			<h4><?= esc_html($attributes['description'] ?: __('Price', 'ctx-events')) ?></h4>
			<div class="event-details-data"><?= esc_html($overwritePrice ?: ($priceDisplay ?? '')) ?></div>
		</div>
	</div>
<?php endif; ?>
