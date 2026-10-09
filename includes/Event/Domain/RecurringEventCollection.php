<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Event\Domain\ValueObjects\RecurrenceIds;
use Contexis\Events\Shared\Domain\Abstract\Collection;

/**
 * @extends Collection<RecurringEvent>
 */
final readonly class RecurringEventCollection extends Collection
{
    public static function from(RecurringEvent ...$recurringEvents): self
    {
        return new self($recurringEvents);
    }

    public function ids(): RecurrenceIds
    {
        return RecurrenceIds::from(
            ...array_map(static fn (RecurringEvent $event) => $event->id, $this->items),
        );
    }
}
