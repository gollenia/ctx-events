<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\BlockEventLoader;

$event = BlockEventLoader::load(get_the_ID());
if (!$event) {
    return;
}

$person = $event->personDto;
if (!$person) {
    return;
}

$nameParts = array_filter([$person->honorificPrefix, $person->givenName, $person->familyName]);
$displayName = implode(' ', $nameParts);

$linkTo = $attributes['linkTo'] ?? '';
$url = match ($linkTo) {
    'mail' => 'mailto:' . ($person->email?->toString() ?? ''),
    'call' => 'tel:' . ($person->telephone ?? ''),
    'custom' => $attributes['url'] ?? '',
    default => '',
};

$linkIcon = $linkTo;
if ($linkTo === 'custom') {
    $linkIcon = 'link';
} elseif ($linkTo === 'public') {
    $linkIcon = 'link';
}

?>

<div class="event-details-item">
	<div class="event-details-image">
		<?= BlockEventLoader::renderIcon($attributes['icon'] ?: 'speaker') ?>
	</div>
	<div class="event-details-text">
		<h4><?= esc_html($attributes['description'] ?: __('Speaker', 'ctx-events')) ?></h4>
		<div class="event-details-data"><?= esc_html($displayName) ?></div>
	</div>
	<?php if (($attributes['showLink'] ?? false) && $url) : ?>
		<div class="event-details-action">
			<a target="_blank" href="<?= esc_url($url) ?>">
				<?= BlockEventLoader::renderIcon($linkIcon) ?>
			</a>
		</div>
	<?php endif; ?>
</div>
