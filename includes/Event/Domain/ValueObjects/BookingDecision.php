<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain\ValueObjects;

use Contexis\Events\Event\Domain\Enums\BookingDenyReason;

final class BookingDecision
{
    private function __construct(
        public readonly bool $allowed,
        public readonly ?BookingDenyReason $reason = null
    ) {
    }

    public static function allow(): self
    {
        return new self(true);
    }

    public static function deny(BookingDenyReason $reason): self
    {
        return new self(false, $reason);
    }
}
