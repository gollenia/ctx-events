<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Application\Contracts\EventOptions;
use Contexis\Events\Event\Application\DTOs\EventCalendarCriteria;
use Contexis\Events\Event\Application\DTOs\EventCriteria;
use Contexis\Events\Event\Application\EventPageCriteria;
use Contexis\Events\Event\Domain\Enums\EventOrderBy;
use Contexis\Events\Event\Domain\Enums\TimeScope;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Shared\Infrastructure\ValueObjects\OrderBy;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;
use Contexis\Events\Shared\Infrastructure\Abstracts\WpQueryBuilder;
use Contexis\Events\Shared\Infrastructure\ValueObjects\Order;

final class WpEventQueryBuilder extends WpQueryBuilder
{
    public static function fromCalendarCriteria(EventCalendarCriteria $criteria): self
    {
        $builder = new self()
            ->withPostType(EventPost::POST_TYPE)
            ->withStatus(StatusList::public())
            ->withTaxonomy(EventTaxonomy::CATEGORIES, $criteria->categories)
            ->withDateOverlap($criteria->startDate, $criteria->endDate)
            ->orderBy(OrderBy::fromMeta(EventMeta::EVENT_START, Order::ASC))
            ->addArg('posts_per_page', -1);

        if ($criteria->locationId !== null) {
            $builder = $builder->withMetaEquals(EventMeta::LOCATION_ID, (string) $criteria->locationId);
        }

        if ($criteria->personId !== null) {
            $builder = $builder->withMetaEquals(EventMeta::PERSON_ID, (string) $criteria->personId);
        }

        return $builder;
    }

    public static function fromCriteria(EventCriteria $criteria): self
    {
        $builder = new self()
            ->withPostType(EventPost::POST_TYPE)
            ->withPagination($criteria->page, $criteria->perPage)
            ->withStatus($criteria->status ?? StatusList::public())
            ->withTaxonomy(EventTaxonomy::CATEGORIES, $criteria->categories)
            ->withTaxonomy(EventTaxonomy::TAGS, $criteria->tags);

        if ($criteria->location !== null) {
            $builder = $builder->withMetaEquals(EventMeta::LOCATION_ID, (string) $criteria->location);
        }
		
        if ($criteria->bookable !== null) {
            $builder = $builder->withMetaEquals(EventMeta::BOOKING_ENABLED, (string) $criteria->bookable);
        }

        if ($criteria->person !== null) {
            $builder = $builder->withMetaEquals(EventMeta::PERSON_ID, (string) $criteria->person);
        }

		foreach (self::dateScopeToMetaQuery($criteria->scope) as $cond) {
			$builder->args['meta_query'][] = $cond;
		}
        

		if($criteria->search) {
			$builder = $builder->withSearch($criteria->search);
		}

		$builder = match ($criteria->isFree) {
			true    => $builder->withMetaEquals(EventMeta::CACHED_MIN_PRICE, '0'),
			false   => $builder->withMetaCompare(EventMeta::CACHED_MIN_PRICE, '0', '>', 'NUMERIC'),
			default => $builder,
		};

        $orderBy = self::mapOrderBy($criteria->orderBy);
        $builder = $builder->orderBy($orderBy);

        return $builder;
    }

    public function withDateOverlap(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): static
    {
        return $this->withMetaQuery([
            'key' => EventMeta::EVENT_START,
            'value' => $endDate->format('Y-m-d 23:59:59'),
            'compare' => '<=',
            'type' => 'DATETIME',
        ])->withMetaQuery([
            'relation' => 'OR',
            [
                'key' => EventMeta::EVENT_END,
                'value' => $startDate->format('Y-m-d 00:00:00'),
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
                    'value' => $startDate->format('Y-m-d 00:00:00'),
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
                    'value' => $startDate->format('Y-m-d 00:00:00'),
                    'compare' => '>=',
                    'type' => 'DATETIME',
                ],
            ],
        ]);
    }

    private static function mapOrderBy(OrderBy $orderBy): OrderBy
    {
		
        return match ($orderBy->field) {
			EventOrderBy::EVENT_TITLE->value      => OrderBy::fromField('post_title', $orderBy->order),
            EventOrderBy::EVEN_START->value     => OrderBy::fromMeta(EventMeta::EVENT_START, $orderBy->order),
            EventOrderBy::BOOKING_START->value  => OrderBy::fromMeta(EventMeta::BOOKING_START, $orderBy->order),
            EventOrderBy::BOOKING_ENABLED->value   => OrderBy::fromMeta(EventMeta::BOOKING_ENABLED, $orderBy->order),
            EventOrderBy::LOCATION->value      => OrderBy::fromMeta(EventMeta::LOCATION_ID, $orderBy->order),
            EventOrderBy::PERSON->value        => OrderBy::fromMeta(EventMeta::PERSON_ID, $orderBy->order),
            EventOrderBy::PRICE->value         => OrderBy::fromMeta(EventMeta::CACHED_MIN_PRICE, $orderBy->order),
            default         => OrderBy::fromMeta(EventMeta::EVENT_START, $orderBy->order),
        };
    }

    private static function dateScopeToMetaQuery(TimeScope $scope): array
    {
        $now = new \DateTimeImmutable('now', wp_timezone());
        $date = $now->format('Y-m-d');
        $dateTime = $now->format('Y-m-d H:i:s');
        $field = get_option(WpEventOptions::EVENT_ONGOING_IS_PAST) ? EventMeta::EVENT_END : EventMeta::EVENT_START;

        return match ($scope) {
            $scope::PAST    => [['key' => $field, 'value' => $dateTime, 'compare' => '<', 'type' => 'DATETIME']],
            $scope::FUTURE  => [
				['key' => $field, 'value' => $dateTime, 'compare' => '>', 'type' => 'DATETIME']
			],
            $scope::TODAY   => [
                ['key' => EventMeta::EVENT_START, 'value' => $date, 'compare' => '<=', 'type' => 'DATE'],
                ['key' => EventMeta::EVENT_END,   'value' => $date, 'compare' => '>=', 'type' => 'DATE'],
            ],
            $scope::TOMORROW => [
                ['key' => EventMeta::EVENT_START, 'value' => $now->modify('+1 day')->format('Y-m-d'), 'compare' => '=', 'type' => 'DATE'],
            ],
            $scope::ONE_WEEK => [
                ['key' => EventMeta::EVENT_START, 'value' => [
                    $date,
                    $now->modify('+7 days')->format('Y-m-d')
                ],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ],
            ],
			$scope::THIS_WEEK   => [
				['key' => EventMeta::EVENT_START, 'value' => [
					$now->modify('monday this week')->format('Y-m-d'),
					$now->modify('sunday this week')->format('Y-m-d')
				],
					'compare' => 'BETWEEN',
					'type' => 'DATE'
				],
			],
            $scope::THIS_MONTH    => [
                ['key' => EventMeta::EVENT_START, 'value' => [
                    $now->modify('first day of this month')->format('Y-m-d'),
                    $now->modify('last day of this month')->format('Y-m-d')
                ],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ],
            ],
            $scope::NEXT_MONTH => [
                ['key' => EventMeta::EVENT_START, 'value' => [
                    $now->modify('first day of next month')->format('Y-m-d'),
                    $now->modify('last day of next month')->format('Y-m-d')
                ],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ],
            ],
			$scope::ONE_MONTH    => [
				['key' => EventMeta::EVENT_START, 'value' => [
					$date,
					$now->modify('+1 month')->format('Y-m-d')
				],
					'compare' => 'BETWEEN',
					'type' => 'DATE'
				],
			],
			$scope::TWO_MONTHS   => [
				['key' => EventMeta::EVENT_START, 'value' => [
					$date,
					$now->modify('+2 months')->format('Y-m-d')
				],
					'compare' => 'BETWEEN',
					'type' => 'DATE'
				],
			],
			$scope::THREE_MONTHS => [
				['key' => EventMeta::EVENT_START, 'value' => [
					$date,
					$now->modify('+3 months')->format('Y-m-d')
				],
					'compare' => 'BETWEEN',	
					'type' => 'DATE'
				],
			],
            $scope::THIS_YEAR     => [
                ['key' => EventMeta::EVENT_START, 'value' => [
                    $now->modify('first day of January')->format('Y-m-d'),
                    $now->modify('last day of December')->format('Y-m-d')
                ],
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                ],
            ],
			$scope::ONE_YEAR      => [
				['key' => EventMeta::EVENT_START, 'value' => [
					$date,
					$now->modify('+1 year')->format('Y-m-d')
				],
					'compare' => 'BETWEEN',
					'type' => 'DATE'
				],
			],
            default    => [['key' => $field, 'value' => $dateTime, 'compare' => '>', 'type' => 'DATETIME']]

        };
    }

}
