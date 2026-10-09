<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Event\Domain\ValueObjects\OccurrenceWindow;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceIds;

interface OccurrenceExceptionRepository
{
    /**
     */
    public function findForRecurringEvents(RecurrenceIds $recurringEventIds, OccurrenceWindow $window): OccurrenceExceptionCollection;
}
