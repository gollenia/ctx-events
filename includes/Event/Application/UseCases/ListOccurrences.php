<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\UseCases;

use Contexis\Events\Event\Application\DTOs\EventCriteria;
use Contexis\Events\Event\Application\DTOs\OccurrenceResponse;
use Contexis\Events\Event\Application\DTOs\OccurrenceResponseCollection;
use Contexis\Events\Event\Application\Service\OccurrenceWindowFactory;
use Contexis\Events\Event\Application\Service\RecurringOccurrenceLister;
use Contexis\Events\Event\Domain\Enums\EventStatus;
use Contexis\Events\Event\Domain\OccurrenceCriteria;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventStatusList;
use Contexis\Events\Shared\Application\ValueObjects\Pagination;
use Contexis\Events\Shared\Domain\Contracts\Clock;

/** Combines persisted events with bounded, virtual recurring occurrences. */
final readonly class ListOccurrences
{
    public function __construct(
        private EventRepository $eventRepository,
        private RecurringOccurrenceLister $recurringOccurrenceLister,
        private OccurrenceWindowFactory $occurrenceWindowFactory,
        private Clock $clock,
    ) {
    }

    public function execute(EventCriteria $criteria): OccurrenceResponseCollection
    {
        $window = $this->occurrenceWindowFactory->fromScope($criteria->scope, $this->clock->now());
        $occurrenceCriteria = new OccurrenceCriteria(
            window: $window,
            filters: $criteria->filters,
            statuses: $this->statuses($criteria),
            recurrenceId: $criteria->recurrenceId,
        );
        $items = [];

        // The existing event repository remains the source of actual posts.
        // Request all candidates here; pagination is applied only after both
        // sources have been merged and chronologically ordered.
        $events = $criteria->recurrenceId === null ? $this->eventRepository->search(new EventCriteria(
            page: 1,
            perPage: PHP_INT_MAX,
            orderBy: $criteria->orderBy,
            scope: $criteria->scope,
            status: $criteria->status,
            isFree: $criteria->isFree,
            bookable: $criteria->bookable,
            filters: $criteria->filters,
        )) : [];

        foreach ($events as $event) {
            if ($event->startDate < $window->endsAt && $event->endDate > $window->startsAt) {
                $items[] = OccurrenceResponse::fromEvent($event);
            }
        }

        // Series deliberately have no booking model. A request for bookable
        // events must therefore never project a virtual occurrence.
        if ($criteria->bookable !== true) {
            foreach ($this->recurringOccurrenceLister->list($occurrenceCriteria) as $recurringOccurrence) {
                $items[] = OccurrenceResponse::fromRecurringOccurrence($recurringOccurrence);
            }
        }

        usort($items, static function (OccurrenceResponse $left, OccurrenceResponse $right): int {
            $byStart = $left->startDate <=> $right->startDate;

            return $byStart !== 0 ? $byStart : $left->occurrenceId <=> $right->occurrenceId;
        });

        $pagination = Pagination::of(count($items), $criteria->page, $criteria->perPage);
        $offset = ($criteria->page - 1) * $criteria->perPage;

        return OccurrenceResponseCollection::from(...array_slice($items, $offset, $criteria->perPage))
            ->withPagination($pagination);
    }

    private function statuses(EventCriteria $criteria): EventStatusList
    {
        if ($criteria->status === null) {
            return EventStatusList::public();
        }

        return EventStatusList::of(...array_map(
            static fn ($status): EventStatus => EventStatus::from($status->value),
            $criteria->status->allStatuses(),
        ));
    }
}
