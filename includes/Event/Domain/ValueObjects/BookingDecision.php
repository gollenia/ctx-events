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

    public function message(): string
    {
        return match ($this->reason) {
            BookingDenyReason::DISABLED   => 'Booking is disabled for this event.',
            BookingDenyReason::NO_CAPACITY => 'No capacity available.',
            BookingDenyReason::NOT_STARTED => 'Booking period has not started yet.',
            BookingDenyReason::ENDED      => 'Booking period has ended.',
            BookingDenyReason::SOLD_OUT   => 'Tickets are sold out.',
            BookingDenyReason::FORM_ERROR => 'Form error.',
            default => '',
        };
    }
}
