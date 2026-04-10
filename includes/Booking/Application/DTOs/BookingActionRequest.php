<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\DTOs;

final readonly class BookingActionRequest
{
    public function __construct(
        public string $reference,
        public bool $sendMail = true,
        public ?string $cancellationReason = null,
    ) {
    }
}
