<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Event\Domain\Enums\OccurrenceState;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * A concrete slot produced by a recurring event rule.
 */
final readonly class Occurrence
{
    private function __construct(
        public OccurrenceKey $key,
        public DateTimeImmutable $startsAt,
        public DateTimeImmutable $endsAt,
        public OccurrenceState $state,
        public ?EventId $detachedEventId,
    ) {
        if ($this->endsAt <= $this->startsAt) {
            throw new InvalidArgumentException('An occurrence must end after it starts.');
        }

        if (($this->state === OccurrenceState::Detached) !== ($this->detachedEventId !== null)) {
            throw new InvalidArgumentException('Only a detached occurrence may reference an event.');
        }
    }

    public static function virtual(OccurrenceKey $key, DateTimeImmutable $startsAt, DateTimeImmutable $endsAt): self
    {
        return new self($key, $startsAt, $endsAt, OccurrenceState::Virtual, null);
    }

    public static function detached(OccurrenceKey $key, DateTimeImmutable $startsAt, DateTimeImmutable $endsAt, EventId $eventId): self
    {
        return new self($key, $startsAt, $endsAt, OccurrenceState::Detached, $eventId);
    }
}
