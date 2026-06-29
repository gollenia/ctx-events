<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\DTOs;

final readonly class CancelBookingAttendeeRequest
{
    public function __construct(
        public string $reference,
        public int $attendeeId,
        public int $cancellationAmountCents,
        public bool $sendMail = true,
    ) {
    }
}
