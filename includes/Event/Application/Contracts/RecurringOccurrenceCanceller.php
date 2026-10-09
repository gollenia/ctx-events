<?php
declare(strict_types=1);
namespace Contexis\Events\Event\Application\Contracts;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
interface RecurringOccurrenceCanceller { public function cancel(RecurrenceId $recurrenceId, OccurrenceKey $occurrenceKey): void; }
