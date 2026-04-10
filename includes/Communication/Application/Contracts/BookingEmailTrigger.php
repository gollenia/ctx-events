<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\Contracts;

use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Communication\Application\DTOs\BookingEmailResult;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;

interface BookingEmailTrigger
{
    public function trigger(
        EmailTrigger $trigger,
        BookingId $bookingId,
        ?string $cancellationReason = null,
    ): BookingEmailResult;
}
