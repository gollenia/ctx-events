<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * An end-exclusive window for projecting occurrence start times.
 */
final readonly class OccurrenceWindow
{
    public function __construct(
        public DateTimeImmutable $startsAt,
        public DateTimeImmutable $endsAt,
    ) {
        if ($this->endsAt <= $this->startsAt) {
            throw new InvalidArgumentException('An occurrence window must end after it starts.');
        }

        if ($this->endsAt > $this->startsAt->modify('+12 months')) {
            throw new InvalidArgumentException('An occurrence window may not exceed twelve months.');
        }
    }
}
