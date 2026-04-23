<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure\Bindings;

use Contexis\Events\Person\Infrastructure\PersonMeta;
use WP_Block;
use WP_Post;

final class PersonBindingSource
{
	public const SOURCE_NAME = 'ctx-events/person';

	public function register(): void
	{
		if (did_action('init')) {
			$this->registerBindingSource();
			return;
		}

		add_action('init', [$this, 'registerBindingSource']);
	}

	public function registerBindingSource(): void
	{
		if (!function_exists('register_block_bindings_source')) {
			return;
		}

		register_block_bindings_source(
			self::SOURCE_NAME,
			[
				'label' => __('Person', 'ctx-events'),
				'uses_context' => ['ctx-events/eventId', 'postId', 'postType'],
				'get_value_callback' => [$this, 'getValue'],
			]
		);
	}

	/**
	 * @param array<string, mixed> $source_args
	 */
	public function getValue(array $source_args, WP_Block $block_instance, string $attribute_name): mixed
	{
		$field = isset($source_args['field']) && is_string($source_args['field'])
			? $source_args['field']
			: '';

		if ($field === '') {
			return '';
		}

		$person = EventBindingContext::getPersonPost($block_instance);

		return match ($field) {
			'name' => $this->getSpeakerName($person),
			'url' => $this->getSpeakerUrl($person),
			'imageUrl' => $this->getSpeakerImageUrl($person),
			'imageAlt' => $this->getSpeakerImageAlt($person),
			'imageId' => $this->getSpeakerImageId($person),
			default => '',
		};
	}

	private function getSpeakerName(?WP_Post $person): string
	{
		if (!$person instanceof WP_Post) {
			return '';
		}

		$nameParts = array_filter([
			(string) get_post_meta($person->ID, PersonMeta::PREFIX, true),
			(string) get_post_meta($person->ID, PersonMeta::FIRST_NAME, true),
			(string) get_post_meta($person->ID, PersonMeta::LAST_NAME, true),
		]);

		if ($nameParts !== []) {
			return implode(' ', $nameParts);
		}

		return get_the_title($person);
	}

	private function getSpeakerUrl(?WP_Post $person): string
	{
		if (!$person instanceof WP_Post) {
			return '';
		}

		$url = get_permalink($person);

		return is_string($url) ? $url : '';
	}

	private function getSpeakerImageUrl(?WP_Post $person): string
	{
		if (!$person instanceof WP_Post) {
			return '';
		}

		$imageUrl = get_the_post_thumbnail_url($person->ID, 'large');

		return is_string($imageUrl) ? $imageUrl : '';
	}

	private function getSpeakerImageAlt(?WP_Post $person): string
	{
		$imageId = $this->getSpeakerImageId($person);
		if ($imageId === 0) {
			return $this->getSpeakerName($person);
		}

		$alt = (string) get_post_meta($imageId, '_wp_attachment_image_alt', true);

		return $alt !== '' ? $alt : $this->getSpeakerName($person);
	}

	private function getSpeakerImageId(?WP_Post $person): int
	{
		if (!$person instanceof WP_Post) {
			return 0;
		}

		return (int) get_post_thumbnail_id($person->ID);
	}
}
