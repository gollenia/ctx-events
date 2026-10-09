<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\UseCases;

use Contexis\Events\Event\Application\Contracts\RecurringOccurrenceDetacher;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use Contexis\Events\Shared\Application\Contracts\UseCase;
use DomainException;

final readonly class DetachRecurringOccurrence implements UseCase
{
    public function __construct(private RecurringOccurrenceDetacher $detacher)
    {
    }

    public function execute(RecurrenceId $recurrenceId, OccurrenceKey $occurrenceKey): EventId
    {
        if (!$occurrenceKey->recurringEventId->equals($recurrenceId)) {
            throw new DomainException('The occurrence does not belong to this recurring event.');
        }

        return $this->detacher->detach($recurrenceId, $occurrenceKey);
    }
}
