<?php
declare(strict_types = 1);

namespace Contexis\Events\Event\Domain\Signals;

use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Domain\Abstract\Signal;

final class EventAvailabilityChanged extends Signal
{
    public const NAME = 'ctx.event.availability.changed';

    public function __construct(
        public EventId $eventId,
		public ?BookingId $bookingId = null,
    ) {
        parent::__construct();
    }
}
