<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class BookingTokenRecord
{
    public function __construct(
        public string $tokenId,
        public int $eventId,
        public string $sessionHash,
        public DateTimeImmutable $expiresAt,
        public bool $used = false,
    ) {
        if ($this->tokenId === '') {
            throw new InvalidArgumentException('Token id cannot be empty.');
        }

        if ($this->eventId <= 0) {
            throw new InvalidArgumentException('Event id must be greater than zero.');
        }

        if ($this->sessionHash === '') {
            throw new InvalidArgumentException('Session hash cannot be empty.');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            tokenId: (string) ($data['tokenId'] ?? ''),
            eventId: (int) ($data['eventId'] ?? 0),
            sessionHash: (string) ($data['sessionHash'] ?? ''),
            expiresAt: new DateTimeImmutable((string) ($data['expiresAt'] ?? 'now')),
            used: (bool) ($data['used'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'tokenId' => $this->tokenId,
            'eventId' => $this->eventId,
            'sessionHash' => $this->sessionHash,
            'expiresAt' => $this->expiresAt->format(DateTimeImmutable::ATOM),
            'used' => $this->used,
        ];
    }

    public function isExpiredAt(DateTimeImmutable $now): bool
    {
        return $now >= $this->expiresAt;
    }
}
