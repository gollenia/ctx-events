<?php
declare(strict_types=1);
namespace Contexis\Events\Event\Application\UseCases;
use Contexis\Events\Event\Application\Contracts\RecurringOccurrenceCanceller;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use DomainException;
final readonly class CancelRecurringOccurrence
{
    public function __construct(private RecurringOccurrenceCanceller $canceller) {}
    public function execute(RecurrenceId $recurrenceId, OccurrenceKey $occurrenceKey): void
    {
        if (!$occurrenceKey->recurringEventId->equals($recurrenceId)) throw new DomainException('The occurrence does not belong to this recurring event.');
        $this->canceller->cancel($recurrenceId, $occurrenceKey);
    }
}
