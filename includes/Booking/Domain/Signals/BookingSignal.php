<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\Signals;

use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Shared\Domain\Abstract\Signal;

abstract class BookingSignal extends Signal
{
    public function __construct(
        public readonly BookingId $bookingId,
    ) {
        parent::__construct();
    }
}
