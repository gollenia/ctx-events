<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure\Bindings;

use Contexis\Events\Event\Application\DTOs\EventCriteria;
use Contexis\Events\Event\Application\DTOs\EventResponse;
use Contexis\Events\Event\Domain\Enums\TimeScope;
use Contexis\Events\Event\Infrastructure\BlockEventLoader;
use Contexis\Events\Event\Infrastructure\EventMeta;
use Contexis\Events\Event\Infrastructure\EventPost;
use Contexis\Events\Event\Infrastructure\WpEventQueryBuilder;
use Contexis\Events\Location\Infrastructure\LocationPost;
use Contexis\Events\Person\Infrastructure\PersonPost;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;
use Contexis\Events\Shared\Infrastructure\ValueObjects\Order;
use Contexis\Events\Shared\Infrastructure\ValueObjects\OrderBy;
use WP_Block;
use WP_Post;

final class EventBindingContext
{
	/** @var array<string, int> */
	private static array $queryCache = [];

	public static function getEventPost(WP_Block $blockInstance): ?WP_Post
	{
		$context = is_array($blockInstance->context ?? null) ? $blockInstance->context : [];
		$eventId = self::resolveEventIdFromContext($context);
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

	/**
	 * @param array<string, mixed> $context
	 */
	public static function resolveEventIdFromContext(array $context): int
	{
		$selectionMode = is_string($context['ctx-events/selectionMode'] ?? null)
			? $context['ctx-events/selectionMode']
			: 'manual';
		$currentEventId =
			($context['postType'] ?? null) === EventPost::POST_TYPE
				? (int) ($context['postId'] ?? 0)
				: 0;
		$selectedEventId = isset($context['ctx-events/eventId'])
			? (int) $context['ctx-events/eventId']
			: 0;

		return match ($selectionMode) {
			'current' => $currentEventId,
			'query' => self::resolveQueryEventId($context),
			default => $selectedEventId > 0 ? $selectedEventId : $currentEventId,
		};
	}

	/**
	 * @param array<string, mixed> $context
	 */
	private static function resolveQueryEventId(array $context): int
	{
		$categories = self::normalizeIdArray($context['ctx-events/queryCategoryIds'] ?? []);
		$tags = self::normalizeIdArray($context['ctx-events/queryTagIds'] ?? []);
		$locationId = isset($context['ctx-events/queryLocationId'])
			? (int) $context['ctx-events/queryLocationId']
			: 0;
		$scope = is_string($context['ctx-events/queryScope'] ?? null)
			? TimeScope::tryFrom($context['ctx-events/queryScope']) ?? TimeScope::FUTURE
			: TimeScope::FUTURE;

		$key = md5((string) wp_json_encode([
			'categories' => $categories,
			'tags' => $tags,
			'location' => $locationId,
			'scope' => $scope->value,
		]));

		if (isset(self::$queryCache[$key])) {
			return self::$queryCache[$key];
		}

		$criteria = new EventCriteria(
			page: 1,
			perPage: 1,
			orderBy: OrderBy::fromField('date-time', Order::ASC),
			scope: $scope,
			categories: $categories,
			tags: $tags,
			status: StatusList::public(),
			location: $locationId > 0 ? $locationId : null,
			person: null,
			isFree: null,
			bookable: null,
			search: null,
		);

		$query = WpEventQueryBuilder::fromCriteria($criteria)->toWpQuery();
		$post = $query->posts[0] ?? null;

		return self::$queryCache[$key] =
			$post instanceof WP_Post && $post->post_type === EventPost::POST_TYPE
				? (int) $post->ID
				: 0;
	}

	/**
	 * @param mixed $value
	 * @return array<int, int>
	 */
	private static function normalizeIdArray(mixed $value): array
	{
		if (!is_array($value)) {
			return [];
		}

		return array_values(array_filter(array_map('intval', $value), static fn (int $id): bool => $id > 0));
	}
}
