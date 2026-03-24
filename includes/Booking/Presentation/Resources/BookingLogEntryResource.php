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
        public string $level,
        public string $timestamp,
        public int $actorId,
        public string $actorName,
        public ?string $message,
    ) {
    }

    public static function fromLogEntry(LogEntry $entry): self
    {
        return new self(
            eventType: $entry->eventType->value,
            level: $entry->level->value,
            timestamp: $entry->timestamp->format(DATE_ATOM),
            actorId: $entry->actor->id,
            actorName: $entry->actor->name,
            message: $entry->message,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'eventType' => $this->eventType,
            'level' => $this->level,
            'timestamp' => $this->timestamp,
            'actorId' => $this->actorId,
            'actorName' => $this->actorName,
            'message' => $this->message,
        ];
    }
}
