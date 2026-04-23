<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\BlockEventLoader;
use Contexis\Events\Shared\Infrastructure\Icons\BlockIconRenderer;

$event = BlockEventLoader::load(get_the_ID());
if (!$event) {
    return;
}

$location = $event->locationDto;
if (!$location) {
    return;
}

$blockAttributes = get_block_wrapper_attributes();
$hasPhoto = str_contains($blockAttributes, 'is-style-photo');
$photo = get_the_post_thumbnail_url($location->id, 'post-thumbnail');

?>

<div class="event-details-item">
	<div class="event-details-image">
		<?php if ($hasPhoto && $photo) : ?>
			<img class="event-details-image" src="<?= esc_url($photo) ?>" alt="<?= esc_attr($location->name) ?>">
		<?php else : ?>
			<?= BlockIconRenderer::render($attributes['icon'] ?: 'location') ?>
		<?php endif; ?>
	</div>
	<div class="event-details-text">
		<h4 class="event-details-title"><?= esc_html($attributes['description'] ?: __('Location', 'ctx-events')) ?></h4>
		<address class="event-details-data">
			<?php if ($attributes['showTitle'] ?? false) : ?>
				<?= esc_html($location->name) ?><br>
			<?php endif; ?>
			<?php if ($attributes['showAddress'] ?? false) : ?>
				<?= esc_html($location->address->streetAddress) ?><br>
			<?php endif; ?>
			<?php if ($attributes['showZip'] ?? false) : ?><?= esc_html($location->address->postalCode) ?> <?php endif; ?>
			<?php if ($attributes['showCity'] ?? false) : ?><?= esc_html($location->address->addressLocality) ?><br><?php endif; ?>
			<?php if ($attributes['showCountry'] ?? false) : ?>
				<?= esc_html($location->address->addressCountry) ?>
			<?php endif; ?>
		</address>
	</div>
	<?php if ($attributes['showLink'] ?? false) : ?>
		<div class="event-details-action">
			<a target="_blank" href="<?= esc_url($attributes['url'] ?: $location->externalUrl) ?>">
				<?= BlockIconRenderer::render('directions') ?>
			</a>
		</div>
	<?php endif; ?>
</div>
