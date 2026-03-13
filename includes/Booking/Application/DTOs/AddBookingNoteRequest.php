<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\DTOs;

final readonly class AddBookingNoteRequest
{
    public function __construct(
        public string $uuid,
        public string $text,
        public string $author,
    ) {
    }
}
