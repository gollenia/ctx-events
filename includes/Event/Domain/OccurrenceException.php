<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Event\Domain\Enums\OccurrenceExceptionType;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\OccurrenceKey;
use InvalidArgumentException;

/**
 * A deliberate deviation from a recurring event's scheduled occurrence.
 */
final readonly class OccurrenceException
{
    private function __construct(
        public OccurrenceKey $occurrenceKey,
        public OccurrenceExceptionType $type,
        public ?EventId $detachedEventId,
    ) {
        if (($this->type === OccurrenceExceptionType::Detached) !== ($this->detachedEventId !== null)) {
            throw new InvalidArgumentException('Only a detached occurrence exception may reference an event.');
        }
    }

    public static function cancelled(OccurrenceKey $occurrenceKey): self
    {
        return new self($occurrenceKey, OccurrenceExceptionType::Cancelled, null);
    }

    public static function detached(OccurrenceKey $occurrenceKey, EventId $eventId): self
    {
        return new self($occurrenceKey, OccurrenceExceptionType::Detached, $eventId);
    }
}
