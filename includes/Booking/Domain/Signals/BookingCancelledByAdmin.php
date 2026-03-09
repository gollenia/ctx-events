<?php

namespace Contexis\Events\Booking\Domain\Signals;

use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Domain\Abstract\Signal;
use Contexis\Events\Shared\Domain\ValueObjects\Email;

class BookingCancelledByAdmin extends Signal
{

	public const NAME = 'ctx.booking.cancelled_by_admin';
    public function __construct(
		public readonly EventId $event,
        public readonly BookingId $booking,
    ) {}
}