<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Domain\OccurrenceCriteria;
use Contexis\Events\Event\Domain\RecurringEvent;
use Contexis\Events\Event\Domain\RecurringEventCollection;
use Contexis\Events\Event\Domain\RecurringEventRepository;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use Contexis\Events\Event\Infrastructure\Mappers\RecurringEventMapper;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

final class WpRecurringEventRepository implements RecurringEventRepository
{
    public function __construct(private readonly RecurringEventMapper $mapper)
    {
    }

    public function search(OccurrenceCriteria $criteria): RecurringEventCollection
    {
        $query = new \WP_Query(
            WpRecurringEventQueryBuilder::fromCriteria($criteria)
                ->withCache()
                ->getArgs(),
        );

        $series = [];
        foreach ($query->posts as $post) {
            $recurringEvent = $this->mapper->map(new PostSnapshot($post));

            if ($this->mayOccurInWindow($recurringEvent, $criteria)) {
                $series[] = $recurringEvent;
            }
        }

        return RecurringEventCollection::from(...$series);
    }

    public function find(RecurrenceId $id): ?RecurringEvent
    {
        $post = get_post($id->toInt());

        return $post instanceof \WP_Post && $post->post_type === RecurringEventPost::POST_TYPE
            ? $this->mapper->map(new PostSnapshot($post))
            : null;
    }

    private function mayOccurInWindow(RecurringEvent $recurringEvent, OccurrenceCriteria $criteria): bool
    {
        $rule = $recurringEvent->recurrenceRule;

        return $rule->firstOccurrenceStartsAt < $criteria->window->endsAt
            && !$rule->hasEndedBefore($criteria->window->startsAt);
    }
}
