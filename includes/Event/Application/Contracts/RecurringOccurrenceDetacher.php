<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\Contracts;

use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;

/** Creates the persisted event and records the matching detached exception. */
interface RecurringOccurrenceDetacher
{
    public function detach(RecurrenceId $recurrenceId, OccurrenceKey $occurrenceKey): EventId;
}
