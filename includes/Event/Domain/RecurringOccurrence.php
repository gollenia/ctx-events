<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Event\Domain\Enums\OccurrenceState;
use InvalidArgumentException;

/**
 * A virtual occurrence together with the recurring event that defines it.
 */
final readonly class RecurringOccurrence
{
    public function __construct(
        public RecurringEvent $recurringEvent,
        public Occurrence $occurrence,
    ) {
        if (!$this->occurrence->key->recurringEventId->equals($this->recurringEvent->id)) {
            throw new InvalidArgumentException('An occurrence key must belong to its recurring event.');
        }

        if ($this->occurrence->state !== OccurrenceState::Virtual) {
            throw new InvalidArgumentException('A recurring occurrence must be virtual.');
        }
    }
}
