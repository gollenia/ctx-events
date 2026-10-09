<?php

declare(strict_types=1);

use Contexis\Events\Event\Infrastructure\BlockEventLoader;
use Contexis\Events\Shared\Infrastructure\Icons\BlockIconRenderer;

$event = BlockEventLoader::load(get_the_ID());
if (!$event) {
    return;
}

$person = $event->personDto;
if (!$person) {
    return;
}

$nameParts = array_filter(
    [$person->honorificPrefix, $person->givenName, $person->familyName, $person->honorificSuffix],
    static fn (?string $part): bool => $part !== null && trim($part) !== '',
);
$displayName = implode(' ', $nameParts);
$portraitUrl = ($attributes['showPortrait'] ?? true)
    ? get_the_post_thumbnail_url($person->id, 'large')
    : false;

$linkTo = $attributes['linkTo'] ?? '';
$url = match ($linkTo) {
    'mail' => 'mailto:' . ($person->email?->toString() ?? ''),
    'call' => 'tel:' . ($person->telephone ?? ''),
    'custom' => $attributes['url'] ?? '',
    default => '',
};

$linkIcon = match ($linkTo) {
    'mail' => 'email',
    'call' => 'phone',
    default => 'link',
};

?>

<div class="event-details-item">
	<div class="event-details-image<?= $portraitUrl ? ' event-details-image--photo' : '' ?>">
		<?php if ($portraitUrl) : ?>
			<img class="event-details-image__photo" src="<?= esc_url($portraitUrl) ?>" alt="<?= esc_attr($displayName) ?>" />
		<?php else : ?>
			<?= BlockIconRenderer::render(($attributes['icon'] ?? '') ?: 'speaker') ?>
		<?php endif; ?>
	</div>
	<div class="event-details-text">
		<h4 class="event-details-title"><?= esc_html($attributes['description'] ?: __('Speaker', 'ctx-events')) ?></h4>
		<div class="event-details-data"><?= esc_html($displayName) ?></div>
	</div>
	<?php if (($attributes['showLink'] ?? false) && $url) : ?>
		<div class="event-details-action">
			<a target="_blank" href="<?= esc_url($url) ?>">
				<?= BlockIconRenderer::render($linkIcon) ?>
			</a>
		</div>
	<?php endif; ?>
</div>
