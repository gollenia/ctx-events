<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\DTOs;

final readonly class UpdateBookingRequest
{
    public function __construct(
        public string $uuid,
        public array $registration,
        public array $attendees,
        public int $donationCents,
        public array $notes,
        public ?string $gateway,
    ) {
    }
}
