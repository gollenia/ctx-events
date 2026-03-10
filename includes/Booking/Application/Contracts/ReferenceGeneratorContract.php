<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Application\Contracts;

use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;

interface ReferenceGeneratorContract
{
    public function create(): BookingReference;
}
