<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\DTOs;

final readonly class UpdateBookingAttendeeRequest
{
    /**
     * @param array<string, mixed> $attendee
     */
    public function __construct(
        public string $reference,
        public int $attendeeId,
        public array $attendee,
    ) {
    }
}
