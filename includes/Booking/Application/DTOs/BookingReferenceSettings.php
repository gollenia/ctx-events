<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Application\DTOs;

final readonly class BookingReferenceSettings
{
    public function __construct(
        public string $prefix = '',
        public string $suffix = '',
    ) {
    }
}
