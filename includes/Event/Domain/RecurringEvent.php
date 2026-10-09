<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Event\Domain\Enums\EventStatus;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceId;
use Contexis\Events\Event\Domain\ValueObjects\RecurrenceRule;
use Contexis\Events\Location\Domain\LocationId;
use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Person\Domain\PersonId;
use Contexis\Events\Shared\Domain\ValueObjects\AuthorId;
use DateTimeImmutable;

/**
 * The shared, non-bookable definition of a recurring event series.
 */
final readonly class RecurringEvent
{
    public function __construct(
        public RecurrenceId $id,
        public EventStatus $status,
        public string $name,
        public RecurrenceRule $recurrenceRule,
        public DateTimeImmutable $createdAt,
        public AuthorId $authorId,
        public ?string $description = null,
        public ?string $audience = null,
        public ?LocationId $locationId = null,
        public ?PersonId $personId = null,
        public ?ImageId $imageId = null,
    ) {
    }
}
