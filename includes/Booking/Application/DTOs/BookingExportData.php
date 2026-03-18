<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\DTOs;

final readonly class BookingExportData
{
    /**
     * @param BookingExportSheet[] $sheets
     */
    public function __construct(
        public string $fileName,
        public array $sheets,
    ) {
    }
}
