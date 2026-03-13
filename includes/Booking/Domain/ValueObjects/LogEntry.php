<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use Contexis\Events\Booking\Domain\Enums\BookingEvent;
use Contexis\Events\Shared\Domain\ValueObjects\Actor;

final readonly class LogEntry
{
    public function __construct(
        public BookingEvent $eventType,
        public Actor $actor,
		public \DateTimeImmutable $timestamp,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            eventType: BookingEvent::from((string) ($data['eventType'] ?? $data['event_type'] ?? BookingEvent::Updated->value)),
            actor: Actor::fromArray((array) ($data['actor'] ?? [])),
            timestamp: new \DateTimeImmutable((string) ($data['timestamp'] ?? 'now')),
        );
    }

    public function toArray(): array
    {
        return [
            'eventType' => $this->eventType->value,
            'actor' => $this->actor->toArray(),
            'timestamp' => $this->timestamp->format(DATE_ATOM),
        ];
    }
}
