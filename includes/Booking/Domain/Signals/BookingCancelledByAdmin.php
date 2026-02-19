<?php

namespace Contexis\Events\Booking\Domain\Signals;

use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Shared\Domain\ValueObjects\Email;

class BookingCancelledByAdmin
{
    public function __construct(
        public readonly Booking $booking,
        public readonly Email $email,
        public readonly string $message
    ) {}
}