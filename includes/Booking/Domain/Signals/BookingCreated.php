<?php

namespace Contexis\Events\Booking\Domain\Signals;

use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Domain\Abstract\Signal;

class BookingCreated extends Signal
{
	public const NAME = 'ctx.booking.created';
    public function __construct(
		public EventId $eventId,
        public BookingId $bookingId
    ) {}
}