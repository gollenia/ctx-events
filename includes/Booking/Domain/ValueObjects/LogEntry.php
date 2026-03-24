<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use Contexis\Events\Booking\Domain\Enums\BookingLogEvent;
use Contexis\Events\Booking\Domain\Enums\BookingLogLevel;
use Contexis\Events\Shared\Domain\ValueObjects\Actor;

final readonly class LogEntry
{
    public function __construct(
        public BookingLogEvent $eventType,
        public BookingLogLevel $level,
        public Actor $actor,
		public \DateTimeImmutable $timestamp,
        public ?string $message = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            eventType: BookingLogEvent::from((string) ($data['eventType'] ?? $data['event_type'] ?? BookingLogEvent::Updated->value)),
            level: BookingLogLevel::from((string) ($data['level'] ?? BookingLogLevel::Info->value)),
            actor: Actor::fromArray((array) ($data['actor'] ?? [])),
            timestamp: new \DateTimeImmutable((string) ($data['timestamp'] ?? 'now')),
            message: isset($data['message']) ? (string) $data['message'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'eventType' => $this->eventType->value,
            'level' => $this->level->value,
            'actor' => $this->actor->toArray(),
            'timestamp' => $this->timestamp->format(DATE_ATOM),
            'message' => $this->message,
        ];
    }
}
