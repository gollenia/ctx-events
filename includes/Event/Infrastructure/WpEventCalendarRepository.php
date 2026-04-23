<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Application\DTOs\EventCalendarCriteria;
use Contexis\Events\Event\Application\DTOs\EventCalendarEntry;
use Contexis\Events\Event\Domain\EventCalendarRepository;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

final class WpEventCalendarRepository implements EventCalendarRepository
{
	public function search(EventCalendarCriteria $criteria): array
	{
		$args = [
			'post_type' => EventPost::POST_TYPE,
			'post_status' => ['publish', 'future'],
			'posts_per_page' => -1,
			'orderby' => 'meta_value',
			'meta_key' => EventMeta::EVENT_START,
			'order' => 'ASC',
			'meta_query' => [
				'relation' => 'AND',
				[
					'key' => EventMeta::EVENT_START,
					'value' => $criteria->endDate->format('Y-m-d 23:59:59'),
					'compare' => '<=',
					'type' => 'DATETIME',
				],
				[
					'relation' => 'OR',
					[
						'key' => EventMeta::EVENT_END,
						'value' => $criteria->startDate->format('Y-m-d 00:00:00'),
						'compare' => '>=',
						'type' => 'DATETIME',
					],
					[
						'relation' => 'AND',
						[
							'key' => EventMeta::EVENT_END,
							'value' => '',
							'compare' => '=',
						],
						[
							'key' => EventMeta::EVENT_START,
							'value' => $criteria->startDate->format('Y-m-d 00:00:00'),
							'compare' => '>=',
							'type' => 'DATETIME',
						],
					],
					[
						'relation' => 'AND',
						[
							'key' => EventMeta::EVENT_END,
							'compare' => 'NOT EXISTS',
						],
						[
							'key' => EventMeta::EVENT_START,
							'value' => $criteria->startDate->format('Y-m-d 00:00:00'),
							'compare' => '>=',
							'type' => 'DATETIME',
						],
					],
				],
			],
		];

		if ($criteria->categories !== []) {
			$args['tax_query'] = [[
				'taxonomy' => EventPost::CATEGORIES,
				'field' => 'term_id',
				'terms' => $criteria->categories,
			]];
		}

		if ($criteria->locationId !== null) {
			$args['meta_query'][] = [
				'key' => EventMeta::LOCATION_ID,
				'value' => (string) $criteria->locationId,
				'compare' => '=',
			];
		}

		$query = new \WP_Query($args);

		$posts = array_filter(
			$query->posts,
			static fn (mixed $post): bool => $post instanceof \WP_Post,
		);

		return array_map(
			static function (\WP_Post $post): EventCalendarEntry {
				$snapshot = new PostSnapshot($post);
				$timezone = $snapshot->getString('_timezone') ?? wp_timezone();
				$locationId = $snapshot->getInt(EventMeta::LOCATION_ID);
				$personMeta = get_post_meta($post->ID, EventMeta::PERSON_ID, true);
				$personIds = is_array($personMeta)
					? array_map('intval', $personMeta)
					: ($personMeta ? [(int) $personMeta] : []);
				$personNames = array_filter(array_map('get_the_title', $personIds));
				$categoryIds = wp_get_post_terms($post->ID, EventPost::CATEGORIES, ['fields' => 'ids']);
				$primaryCategoryId = is_array($categoryIds) && $categoryIds !== []
					? (int) reset($categoryIds)
					: null;
				$color = $primaryCategoryId !== null
					? sanitize_hex_color((string) get_term_meta($primaryCategoryId, 'color', true)) ?: null
					: null;

				return new EventCalendarEntry(
					id: $post->ID,
					title: $snapshot->getString('post_title'),
					description: $snapshot->getString('post_excerpt') ?? '',
					startDate: $snapshot->getDateTime(EventMeta::EVENT_START, $timezone)
						?: new \DateTimeImmutable('1970-01-01 00:00:00', $timezone),
					endDate: $snapshot->getDateTime(EventMeta::EVENT_END, $timezone)
						?: ($snapshot->getDateTime(EventMeta::EVENT_START, $timezone)
							?: new \DateTimeImmutable('1970-01-01 00:00:00', $timezone)),
					categoryIds: is_array($categoryIds) ? array_map('intval', $categoryIds) : [],
					color: $color,
					locationName: $locationId !== null ? get_the_title($locationId) : null,
					personName: $personNames !== [] ? implode(', ', $personNames) : null,
				);
			},
			$posts,
		);
	}
}
