<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Wordpress\DuplicatePost;

class EventDuplicatePost extends DuplicatePost
{
	public function duplicateAt(int $postId, \DateTimeImmutable $targetDate): ?int
	{
		$sourcePost = get_post($postId);
		if (!($sourcePost instanceof \WP_Post) || !$this->supportsPostType($sourcePost->post_type)) {
			return null;
		}

		$sourceStart = $this->dateFromMeta($postId, EventMeta::EVENT_START);
		if ($sourceStart === null) {
			return null;
		}

		$newPostId = $this->duplicate($postId);
		if ($newPostId === null) {
			return null;
		}

		$newStart = $targetDate->setTime(
			(int) $sourceStart->format('H'),
			(int) $sourceStart->format('i'),
			(int) $sourceStart->format('s'),
		);
		$interval = $sourceStart->diff($newStart);
		foreach ([EventMeta::EVENT_START, EventMeta::EVENT_END, EventMeta::BOOKING_START, EventMeta::BOOKING_END] as $metaKey) {
			$date = $this->dateFromMeta($postId, $metaKey);
			if ($date !== null) {
				update_post_meta($newPostId, $metaKey, $date->add($interval)->format('Y-m-d\\TH:i'));
			}
		}

		return $newPostId;
	}

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
			EventMeta::CACHED_MIN_PRICE,
			EventMeta::CACHED_MAX_PRICE,
		], true);
	}

	protected function afterDuplicate(\WP_Post $sourcePost, int $newPostId): void
	{
		$tickets = get_post_meta($newPostId, EventMeta::TICKETS, true);
		if (!is_array($tickets)) {
			return;
		}

		$duplicatedTickets = array_map(static function (mixed $ticket): mixed {
			if (!is_array($ticket)) {
				return $ticket;
			}

			$ticket['ticket_id'] = wp_generate_uuid4();
			return $ticket;
		}, $tickets);

		update_post_meta($newPostId, EventMeta::TICKETS, $duplicatedTickets);
	}

	private function dateFromMeta(int $postId, string $metaKey): ?\DateTimeImmutable
	{
		$value = get_post_meta($postId, $metaKey, true);
		if (!is_string($value) || $value === '') {
			return null;
		}

		try {
			return new \DateTimeImmutable($value, wp_timezone());
		} catch (\Exception) {
			return null;
		}
	}
}
