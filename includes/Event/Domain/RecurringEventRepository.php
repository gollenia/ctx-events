<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;

interface RecurringEventRepository
{
    public function find(RecurrenceId $id): ?RecurringEvent;

    /**
     * Returns only series that may have an occurrence starting in the window.
     *
     */
    public function search(OccurrenceCriteria $criteria): RecurringEventCollection;
}
