<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain\ValueObjects;

use Contexis\Events\Shared\Domain\Abstract\Collection;
use InvalidArgumentException;

/**
 * @extends Collection<RecurrenceId>
 */
final readonly class RecurrenceIds extends Collection
{
    public static function from(RecurrenceId ...$ids): self
    {
        $values = array_map(static fn (RecurrenceId $id): int => $id->toInt(), $ids);
        if (count($values) !== count(array_unique($values))) {
            throw new InvalidArgumentException('A recurrence ID may only appear once.');
        }

        return new self($ids);
    }

    public function contains(RecurrenceId $id): bool
    {
        foreach ($this->items as $item) {
            if ($item->equals($id)) {
                return true;
            }
        }

        return false;
    }
}
