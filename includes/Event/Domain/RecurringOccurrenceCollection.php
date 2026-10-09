<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

/**
 * @extends Collection<RecurringOccurrence>
 */
final readonly class RecurringOccurrenceCollection extends Collection
{
    public static function from(RecurringOccurrence ...$occurrences): self
    {
        return new self($occurrences);
    }
}
