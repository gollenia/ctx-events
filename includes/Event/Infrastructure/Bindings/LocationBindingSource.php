<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure\Bindings;

use Contexis\Events\Location\Infrastructure\LocationMeta;
use WP_Block;
use WP_Post;

final class LocationBindingSource
{
	public const SOURCE_NAME = 'ctx-events/location';

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
				'label' => __('Location', 'ctx-events'),
				'uses_context' => ['ctx-events/eventId', 'postId', 'postType'],
				'get_value_callback' => [$this, 'getValue'],
			]
		);
	}

	/**
	 * @param array<string, mixed> $source_args
	 */
	public function getValue(array $source_args, WP_Block $block_instance, string $attribute_name): string
	{
		$field = isset($source_args['field']) && is_string($source_args['field'])
			? $source_args['field']
			: '';

		if ($field === '') {
			return '';
		}

		$location = EventBindingContext::getLocationPost($block_instance);

		return match ($field) {
			'name' => $this->getLocationTitle($location),
			'url' => $this->getLocationUrl($location),
			'addressLine' => $this->getLocationMeta($location, LocationMeta::ADDRESS),
			'city' => $this->getLocationMeta($location, LocationMeta::CITY),
			'postalCode' => $this->getLocationMeta($location, LocationMeta::POSTCODE),
			'country' => $this->getLocationMeta($location, LocationMeta::COUNTRY),
			default => '',
		};
	}

	private function getLocationTitle(?WP_Post $location): string
	{
		return $location instanceof WP_Post ? get_the_title($location) : '';
	}

	private function getLocationUrl(?WP_Post $location): string
	{
		if (!$location instanceof WP_Post) {
			return '';
		}

		$externalUrl = (string) get_post_meta($location->ID, LocationMeta::URL, true);
		if ($externalUrl !== '') {
			return $externalUrl;
		}

		$permalink = get_permalink($location);

		return is_string($permalink) ? $permalink : '';
	}

	private function getLocationMeta(?WP_Post $location, string $metaKey): string
	{
		return $location instanceof WP_Post
			? (string) get_post_meta($location->ID, $metaKey, true)
			: '';
	}
}
