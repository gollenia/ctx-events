<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Booking\Domain\BookingTokenStore;
use Contexis\Events\Booking\Domain\ValueObjects\BookingTokenRecord;

final class FakeTokenStore implements BookingTokenStore
{
    /** @var array<string, BookingTokenRecord> */
    private array $tokens = [];

    public static function empty(): self
    {
        return new self();
    }

    public static function withToken(BookingTokenRecord $record): self
    {
        $store = new self();
        $store->tokens[$record->tokenId] = $record;
        return $store;
    }

    public function save(BookingTokenRecord $token): void
    {
        $this->tokens[$token->tokenId] = $token;
    }

    public function find(string $tokenId): ?BookingTokenRecord
    {
        return $this->tokens[$tokenId] ?? null;
    }

    public function delete(string $tokenId): void
    {
        unset($this->tokens[$tokenId]);
    }
}
