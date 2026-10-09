<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;
use Stringable;

/**
 * The natural key of an originally scheduled recurring-event slot.
 *
 * Unlike EventId and RecurrenceId, this is not a persistence identifier. It
 * remains stable when a slot is detached and its resulting event is moved.
 */
final readonly class OccurrenceKey implements Stringable
{
    private function __construct(
        public RecurrenceId $recurringEventId,
        public DateTimeImmutable $scheduledStartsAt,
    ) {
    }

    public static function from(RecurrenceId $recurringEventId, DateTimeImmutable $scheduledStartsAt): self
    {
        return new self($recurringEventId, $scheduledStartsAt);
    }

    /** Rehydrates the stable, serialized form stored for a detached slot. */
    public static function fromString(string $value): self
    {
        if (preg_match('/^recurrence:(?<id>[1-9][0-9]*):(?<startsAt>.+)$/', $value, $matches) !== 1) {
            throw new InvalidArgumentException('An occurrence key has an invalid serialized form.');
        }

        try {
            $startsAt = new DateTimeImmutable($matches['startsAt']);
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException('An occurrence key has an invalid scheduled start.', 0, $exception);
        }

        return new self(new RecurrenceId((int) $matches['id']), $startsAt);
    }

    public function equals(self $other): bool
    {
        return $this->recurringEventId->equals($other->recurringEventId)
            && $this->scheduledStartsAt->format('Y-m-d\\TH:i:sP') === $other->scheduledStartsAt->format('Y-m-d\\TH:i:sP');
    }

    public function __toString(): string
    {
        return sprintf(
            'recurrence:%d:%s',
            $this->recurringEventId->toInt(),
            $this->scheduledStartsAt->format('Y-m-d\\TH:i:sP'),
        );
    }
}
