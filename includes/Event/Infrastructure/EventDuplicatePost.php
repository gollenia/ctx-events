<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Wordpress\DuplicatePost;

class EventDuplicatePost extends DuplicatePost
{
	protected function supportsPostType(string $postType): bool
	{
		return in_array($postType, [EventPost::POST_TYPE, RecurringEventPost::POST_TYPE], true);
	}

	protected function shouldDuplicateMetaKey(string $metaKey): bool
	{
		if (!parent::shouldDuplicateMetaKey($metaKey)) {
			return false;
		}

		return !in_array($metaKey, [
			EventMeta::CACHED_AVAILABLE,
			EventMeta::CACHED_BOOKING_STATS,
			EventMeta::CACHED_MIN_PRICE,
			EventMeta::CACHED_MAX_PRICE,
		], true);
	}
}
