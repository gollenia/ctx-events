<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\DTOs;

final readonly class BookingExportSheet
{
    /**
     * @param array<int, array<int, scalar|null>> $rows
     */
    public function __construct(
        public string $name,
        public array $rows,
    ) {
    }
}
