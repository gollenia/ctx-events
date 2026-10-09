<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain\ValueObjects;

use Contexis\Events\Event\Domain\Enums\RecurrenceWeekday;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, RecurrenceWeekday>
 */
final readonly class RecurrenceWeekdays implements Countable, IteratorAggregate
{
    /**
     * @param list<RecurrenceWeekday> $items
     */
    private function __construct(private array $items)
    {
        $values = array_map(static fn (RecurrenceWeekday $weekday): string => $weekday->value, $this->items);

        if (count($values) !== count(array_unique($values))) {
            throw new InvalidArgumentException('A recurrence weekday may only be selected once.');
        }
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public static function of(RecurrenceWeekday ...$weekdays): self
    {
        return new self($weekdays);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function contains(RecurrenceWeekday $weekday): bool
    {
        return in_array($weekday, $this->items, true);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, RecurrenceWeekday>
     */
    public function getIterator(): Traversable
    {
        yield from $this->items;
    }
}
