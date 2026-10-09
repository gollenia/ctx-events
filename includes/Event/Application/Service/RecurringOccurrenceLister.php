<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\Service;

use Contexis\Events\Event\Domain\OccurrenceExceptionRepository;
use Contexis\Events\Event\Domain\OccurrenceCriteria;
use Contexis\Events\Event\Domain\OccurrenceGenerator;
use Contexis\Events\Event\Domain\RecurringEventRepository;
use Contexis\Events\Event\Domain\RecurringOccurrence;
use Contexis\Events\Event\Domain\RecurringOccurrenceCollection;

/**
 * Internal building block for ListOccurrences.
 *
 * Cancelled and detached slots are omitted because a detached slot is
 * represented later by its own individual event.
 */
final readonly class RecurringOccurrenceLister
{
    public function __construct(
        private RecurringEventRepository $recurringEventRepository,
        private OccurrenceExceptionRepository $occurrenceExceptionRepository,
        private OccurrenceGenerator $occurrenceGenerator,
    ) {
    }

    public function list(OccurrenceCriteria $criteria): RecurringOccurrenceCollection
    {
        $recurringEvents = $this->recurringEventRepository->search($criteria);
        $exceptions = $this->occurrenceExceptionRepository->findForRecurringEvents($recurringEvents->ids(), $criteria->window);
        $items = [];

        foreach ($recurringEvents as $recurringEvent) {
            foreach ($this->occurrenceGenerator->generate($recurringEvent->id, $recurringEvent->recurrenceRule, $criteria->window) as $occurrence) {
                if ($exceptions->findFor($occurrence->key) !== null) {
                    continue;
                }

                $items[] = new RecurringOccurrence($recurringEvent, $occurrence);
            }
        }

        usort($items, static function (RecurringOccurrence $left, RecurringOccurrence $right): int {
            $byStart = $left->occurrence->startsAt <=> $right->occurrence->startsAt;

            return $byStart !== 0
                ? $byStart
                : (string) $left->occurrence->key <=> (string) $right->occurrence->key;
        });

        return RecurringOccurrenceCollection::from(...$items);
    }
}
