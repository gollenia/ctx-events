<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Application\DTOs\EventCalendarCriteria;
use Contexis\Events\Event\Application\DTOs\EventCalendarEntry;
use Contexis\Events\Event\Application\Service\RecurringOccurrenceLister;
use Contexis\Events\Event\Domain\EventCalendarRepository;
use Contexis\Events\Event\Domain\OccurrenceCriteria;
use Contexis\Events\Event\Domain\RecurringOccurrence;
use Contexis\Events\Event\Domain\ValueObjects\EventFilters;
use Contexis\Events\Event\Domain\ValueObjects\EventStatusList;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceWindow;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

final class WpEventCalendarRepository implements EventCalendarRepository
{
	public function __construct(private readonly RecurringOccurrenceLister $recurringOccurrenceLister)
	{
	}

	public function search(EventCalendarCriteria $criteria): array
	{
		$query = WpEventQueryBuilder::fromCalendarCriteria($criteria)->toWpQuery();

		$posts = array_filter(
			$query->posts,
			static fn (mixed $post): bool => $post instanceof \WP_Post,
		);

		$entries = array_map(fn (\WP_Post $post): EventCalendarEntry => $this->entryFromPost($post), $posts);
		foreach ($this->recurringOccurrences($criteria) as $occurrence) {
			$entries[] = $this->entryFromRecurringOccurrence($occurrence);
		}

		usort($entries, static fn (EventCalendarEntry $left, EventCalendarEntry $right): int => $left->startDate <=> $right->startDate);

		return $entries;
	}

	/** @return list<RecurringOccurrence> */
	private function recurringOccurrences(EventCalendarCriteria $criteria): array
	{
		$window = new OccurrenceWindow($criteria->startDate, $criteria->endDate->modify('+1 second'));
		$occurrences = $this->recurringOccurrenceLister->list(new OccurrenceCriteria(
			window: $window,
			filters: new EventFilters(
				categories: $criteria->categories,
				locationId: $criteria->locationId,
				personId: $criteria->personId,
			),
			statuses: EventStatusList::public(),
		));

		return $occurrences->toArray();
	}

	private function entryFromRecurringOccurrence(RecurringOccurrence $recurringOccurrence): EventCalendarEntry
	{
		$seriesId = $recurringOccurrence->recurringEvent->id->toInt();
		$locationId = $recurringOccurrence->recurringEvent->locationId?->toInt();
		$personId = $recurringOccurrence->recurringEvent->personId?->toInt();

		return new EventCalendarEntry(
			id: $seriesId,
			title: $recurringOccurrence->recurringEvent->name,
			description: $recurringOccurrence->recurringEvent->description ?? '',
			startDate: $recurringOccurrence->occurrence->startsAt,
			endDate: $recurringOccurrence->occurrence->endsAt,
			categoryIds: $this->categoryIds($seriesId),
			color: $this->color($this->categoryIds($seriesId)),
			locationName: $locationId !== null ? get_the_title($locationId) : null,
			personName: $personId !== null ? get_the_title($personId) : null,
		);
	}

	private function entryFromPost(\WP_Post $post): EventCalendarEntry
	{
		$snapshot = new PostSnapshot($post);
		$timezone = $snapshot->getString('_timezone') ?? wp_timezone();
		$locationId = $snapshot->getInt(EventMeta::LOCATION_ID);
		$personMeta = get_post_meta($post->ID, EventMeta::PERSON_ID, true);
		$personIds = is_array($personMeta) ? array_map('intval', $personMeta) : ($personMeta ? [(int) $personMeta] : []);
		$personNames = array_filter(array_map('get_the_title', $personIds));
		$categoryIds = $this->categoryIds($post->ID);

		return new EventCalendarEntry(
			id: $post->ID,
			title: $snapshot->getString('post_title'),
			description: $snapshot->getString('post_excerpt') ?? '',
			startDate: $snapshot->getDateTime(EventMeta::EVENT_START, $timezone) ?: new \DateTimeImmutable('1970-01-01 00:00:00', $timezone),
			endDate: $snapshot->getDateTime(EventMeta::EVENT_END, $timezone) ?: ($snapshot->getDateTime(EventMeta::EVENT_START, $timezone) ?: new \DateTimeImmutable('1970-01-01 00:00:00', $timezone)),
			categoryIds: $categoryIds,
			color: $this->color($categoryIds),
			locationName: $locationId !== null ? get_the_title($locationId) : null,
			personName: $personNames !== [] ? implode(', ', $personNames) : null,
		);
	}

	/** @return list<int> */
	private function categoryIds(int $postId): array
	{
		$categoryIds = wp_get_post_terms($postId, EventTaxonomy::CATEGORIES, ['fields' => 'ids']);
		return is_array($categoryIds) ? array_map('intval', $categoryIds) : [];
	}

	/** @param list<int> $categoryIds */
	private function color(array $categoryIds): ?string
	{
		$primaryCategoryId = $categoryIds[0] ?? null;
		return $primaryCategoryId !== null ? sanitize_hex_color((string) get_term_meta($primaryCategoryId, 'color', true)) ?: null : null;
	}
}
