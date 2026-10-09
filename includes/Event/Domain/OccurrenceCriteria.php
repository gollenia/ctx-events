<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Event\Domain\ValueObjects\EventFilters;
use Contexis\Events\Event\Domain\ValueObjects\EventStatusList;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceWindow;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;

/** The finite, content-filtered query used to read event occurrences. */
final readonly class OccurrenceCriteria
{
    public function __construct(
        public OccurrenceWindow $window,
        public EventFilters $filters,
        public EventStatusList $statuses,
        public ?RecurrenceId $recurrenceId = null,
    ) {
    }
}
