<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Booking\Domain\BookingTokenStore;
use Contexis\Events\Booking\Domain\ValueObjects\BookingTokenRecord;
use DateTimeImmutable;

final class WpBookingTokenStore implements BookingTokenStore
{
    private const string TRANSIENT_PREFIX = 'ctx_events_booking_token_';

    public function save(BookingTokenRecord $token): void
    {
        $seconds = $token->expiresAt->getTimestamp() - time();
        $ttl = max(1, $seconds);

        set_transient($this->key($token->tokenId), $token->toArray(), $ttl);
    }

    public function find(string $tokenId): ?BookingTokenRecord
    {
        $payload = get_transient($this->key($tokenId));
        if (!is_array($payload)) {
            return null;
        }

        $record = BookingTokenRecord::fromArray($payload);

        if ($record->isExpiredAt(new DateTimeImmutable('now'))) {
            $this->delete($tokenId);
            return null;
        }

        return $record;
    }

    public function delete(string $tokenId): void
    {
        delete_transient($this->key($tokenId));
    }

    private function key(string $tokenId): string
    {
        return self::TRANSIENT_PREFIX . hash('sha256', $tokenId);
    }
}
