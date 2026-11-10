<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Application\DTOs\EventCalendarCriteria;
use Contexis\Events\Event\Application\DTOs\EventCalendarEntry;
use Contexis\Events\Event\Application\Service\RecurringOccurrenceLister;
use Contexis\Events\Event\Domain\EventCalendarRepository;
use Contexis\Events\Event\Domain\OccurrenceCriteria;
use Contexis\Events\Event\Domain\RecurringOccurrence;
use Contexis\Events\Event\Domain\Enums\EventStatus;
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
		$posts = CalendarTiming::measure('event-query', function () use ($criteria): array {
			$query = WpEventQueryBuilder::fromCalendarCriteria($criteria)->toWpQuery();
			return array_filter($query->posts, static fn (mixed $post): bool => $post instanceof \WP_Post);
		});
		$entries = CalendarTiming::measure('event-mapping', function () use ($posts): array {
			$entries = [];
			foreach ($posts as $post) {
				array_push($entries, ...$this->entriesFromPost($post));
			}

			return $entries;
		});
		foreach (CalendarTiming::measure('recurrences', fn (): array => $this->recurringOccurrences($criteria)) as $occurrence) {
			$entries[] = $this->entryFromRecurringOccurrence($occurrence);
		}

		CalendarTiming::measure('sorting', function () use (&$entries): void {
			usort($entries, static fn (EventCalendarEntry $left, EventCalendarEntry $right): int => $left->startDate <=> $right->startDate);
		});

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
			statuses: $criteria->statuses === null
				? EventStatusList::public()
				: EventStatusList::of(...array_map(
					static fn (string $status): EventStatus => EventStatus::from($status),
					$criteria->statuses->toArray(),
				)),
		));

		return $occurrences->toArray();
	}

	private function entryFromRecurringOccurrence(RecurringOccurrence $recurringOccurrence): EventCalendarEntry
	{
		$seriesId = $recurringOccurrence->recurringEvent->id->toInt();
		$locationId = $recurringOccurrence->recurringEvent->locationId?->toInt();
		$personId = $recurringOccurrence->recurringEvent->personId?->toInt();
		$categoryIds = $this->categoryIds($seriesId);

		return new EventCalendarEntry(
			id: (string) $recurringOccurrence->occurrence->key,
			title: $recurringOccurrence->recurringEvent->name,
			description: $recurringOccurrence->recurringEvent->description ?? '',
			startDate: $recurringOccurrence->occurrence->startsAt,
			endDate: $recurringOccurrence->occurrence->endsAt,
			categoryIds: $categoryIds,
			color: $this->color($categoryIds),
			locationName: $locationId !== null ? get_the_title($locationId) : null,
			personName: $personId !== null ? get_the_title($personId) : null,
			recurrenceSeriesId: $seriesId,
			isDraft: $recurringOccurrence->recurringEvent->status === EventStatus::Draft,
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
			id: (string) $post->ID,
			title: $snapshot->getString('post_title'),
			description: $snapshot->getString('post_excerpt') ?? '',
			startDate: $snapshot->getDateTime(EventMeta::EVENT_START, $timezone) ?: new \DateTimeImmutable('1970-01-01 00:00:00', $timezone),
			endDate: $snapshot->getDateTime(EventMeta::EVENT_END, $timezone) ?: ($snapshot->getDateTime(EventMeta::EVENT_START, $timezone) ?: new \DateTimeImmutable('1970-01-01 00:00:00', $timezone)),
			categoryIds: $categoryIds,
			color: $this->color($categoryIds),
			locationName: $locationId !== null ? get_the_title($locationId) : null,
			personName: $personNames !== [] ? implode(', ', $personNames) : null,
			isDraft: $post->post_status === 'draft',
			eventId: $post->ID,
		);
	}

	/** @return list<EventCalendarEntry> */
	private function entriesFromPost(\WP_Post $post): array
	{
		$entry = $this->entryFromPost($post);
		if (!get_post_meta($post->ID, EventMeta::EVENT_DAILY, true)) {
			return [$entry];
		}

		return $this->dailyEntries($entry);
	}

	/** @return list<EventCalendarEntry> */
	private function dailyEntries(EventCalendarEntry $entry): array
	{
		$entries = [];
		$day = $entry->startDate->setTime(0, 0);
		$lastDay = $entry->endDate->setTime(0, 0);

		while ($day <= $lastDay) {
			$start = $day->setTime(
				(int) $entry->startDate->format('H'),
				(int) $entry->startDate->format('i'),
			);
			$end = $day->setTime(
				(int) $entry->endDate->format('H'),
				(int) $entry->endDate->format('i'),
			);

			if ($end > $start) {
				$entries[] = new EventCalendarEntry(
					id: $entry->id . ':' . $day->format('Y-m-d'),
					title: $entry->title,
					description: $entry->description,
					startDate: $start,
					endDate: $end,
					categoryIds: $entry->categoryIds,
					color: $entry->color,
					locationName: $entry->locationName,
					personName: $entry->personName,
					isDraft: $entry->isDraft,
					eventId: $entry->eventId,
				);
			}

			$day = $day->modify('+1 day');
		}

		return $entries;
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
