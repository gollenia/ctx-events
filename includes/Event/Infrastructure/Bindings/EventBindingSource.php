<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure\Bindings;

use Contexis\Events\Event\Infrastructure\BlockEventLoader;
use WP_Block;
use WP_Post;

final class EventBindingSource
{
	use SupportsCoverImageBindings;

	public const SOURCE_NAME = 'ctx-events/event';

	private const TRANSPARENT_IMAGE = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

	public function register(): void
	{
		add_filter(
			'block_bindings_supported_attributes_core/cover',
			[$this, 'extendCoverSupportedAttributes']
		);
		add_filter(
			'render_block_core/cover',
			[$this, 'renderBoundCover'],
			10,
			3
		);

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
				'label' => __('Event', 'ctx-events'),
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

		$event = EventBindingContext::getEventPost($block_instance);
		if (!$event instanceof WP_Post) {
			return '';
		}

		$eventResponse = EventBindingContext::getEventResponse($block_instance);

		return match ($field) {
			'dateLabel' => $eventResponse
				? BlockEventLoader::formatDateRange($eventResponse->startDate, $eventResponse->endDate)
				: '',
			'timeLabel' => $eventResponse
				? BlockEventLoader::formatTimeRange($eventResponse->startDate, $eventResponse->endDate)
				: '',
			'imageAlt' => $this->getImageAlt($event->ID),
			'imageId' => (int) get_post_thumbnail_id($event),
			'imageUrl' => $this->getImageUrl($event->ID),
			'schedule' => $this->getSchedule($eventResponse),
			'title' => get_the_title($event),
			'excerpt' => $this->getExcerpt($event),
			'link' => (string) get_permalink($event),
			default => '',
		};
	}

	private function getExcerpt(WP_Post $event): string
	{
		if (has_excerpt($event)) {
			return wp_strip_all_tags((string) get_the_excerpt($event));
		}

		return wp_trim_words(wp_strip_all_tags((string) $event->post_content), 26);
	}

	private function getSchedule(mixed $eventResponse): string
	{
		if (!$eventResponse) {
			return '';
		}

		$date = BlockEventLoader::formatDateRange($eventResponse->startDate, $eventResponse->endDate);
		$time = BlockEventLoader::formatTimeRange($eventResponse->startDate, $eventResponse->endDate);

		if ($date === '') {
			return '';
		}

		return $time !== '' ? sprintf('%s, %s', $date, $time) : $date;
	}

	private function getImageAlt(int $eventId): string
	{
		$thumbnailId = get_post_thumbnail_id($eventId);
		if (!$thumbnailId) {
			return '';
		}

		return (string) get_post_meta($thumbnailId, '_wp_attachment_image_alt', true);
	}

	private function getImageUrl(int $eventId): string
	{
		$imageUrl = get_the_post_thumbnail_url($eventId, 'large');
		if (!is_string($imageUrl) || $imageUrl === '') {
			return self::TRANSPARENT_IMAGE;
		}

		return $imageUrl;
	}

	protected function getBoundImageUrl(WP_Block $block_instance): string
	{
		$event = EventBindingContext::getEventPost($block_instance);

		return $event instanceof WP_Post ? $this->getImageUrl($event->ID) : '';
	}

	protected function getBoundImageAlt(WP_Block $block_instance): string
	{
		$event = EventBindingContext::getEventPost($block_instance);

		return $event instanceof WP_Post ? $this->getImageAlt($event->ID) : '';
	}

	protected function getBoundImageId(WP_Block $block_instance): int
	{
		$event = EventBindingContext::getEventPost($block_instance);

		return $event instanceof WP_Post ? (int) get_post_thumbnail_id($event) : 0;
	}
}
