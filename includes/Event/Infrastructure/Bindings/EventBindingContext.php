<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure\Bindings;

use Contexis\Events\Event\Application\DTOs\EventResponse;
use Contexis\Events\Event\Infrastructure\BlockEventLoader;
use Contexis\Events\Event\Infrastructure\EventMeta;
use Contexis\Events\Event\Infrastructure\EventPost;
use Contexis\Events\Location\Infrastructure\LocationPost;
use Contexis\Events\Person\Infrastructure\PersonPost;
use WP_Block;
use WP_Post;

final class EventBindingContext
{
	public static function getEventPost(WP_Block $blockInstance): ?WP_Post
	{
		$context = is_array($blockInstance->context ?? null) ? $blockInstance->context : [];
		$selectedEventId = isset($context['ctx-events/eventId']) ? (int) $context['ctx-events/eventId'] : 0;
		$contextPostId =
			($context['postType'] ?? null) === EventPost::POST_TYPE
				? (int) ($context['postId'] ?? 0)
				: 0;

		$eventId = $selectedEventId ?: $contextPostId;
		if ($eventId <= 0) {
			return null;
		}

		$post = get_post($eventId);
		if (!$post instanceof WP_Post || $post->post_type !== EventPost::POST_TYPE) {
			return null;
		}

		return $post;
	}

	public static function getEventResponse(WP_Block $blockInstance): ?EventResponse
	{
		$event = self::getEventPost($blockInstance);
		if (!$event instanceof WP_Post) {
			return null;
		}

		return BlockEventLoader::load($event->ID);
	}

	public static function getPersonPost(WP_Block $blockInstance): ?WP_Post
	{
		return self::getRelatedPost($blockInstance, EventMeta::PERSON_ID, PersonPost::POST_TYPE);
	}

	public static function getLocationPost(WP_Block $blockInstance): ?WP_Post
	{
		return self::getRelatedPost($blockInstance, EventMeta::LOCATION_ID, LocationPost::POST_TYPE);
	}

	private static function getRelatedPost(WP_Block $blockInstance, string $metaKey, string $postType): ?WP_Post
	{
		$event = self::getEventPost($blockInstance);
		if (!$event instanceof WP_Post) {
			return null;
		}

		$relatedId = (int) get_post_meta($event->ID, $metaKey, true);
		if ($relatedId <= 0) {
			return null;
		}

		$post = get_post($relatedId);
		if (!$post instanceof WP_Post || $post->post_type !== $postType) {
			return null;
		}

		return $post;
	}
}
