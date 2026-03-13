<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation\Resources;

use Contexis\Events\Booking\Domain\ValueObjects\LogEntry;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final readonly class BookingLogEntryResource implements Resource
{
    public function __construct(
        public string $eventType,
        public string $timestamp,
        public int $actorId,
        public string $actorName,
    ) {
    }

    public static function fromLogEntry(LogEntry $entry): self
    {
        return new self(
            eventType: $entry->eventType->value,
            timestamp: $entry->timestamp->format(DATE_ATOM),
            actorId: $entry->actor->id,
            actorName: $entry->actor->name,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'eventType' => $this->eventType,
            'timestamp' => $this->timestamp,
            'actorId' => $this->actorId,
            'actorName' => $this->actorName,
        ];
    }
}
