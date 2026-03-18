<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\Signals;

final class BookingConfirmedOnlineSignal extends BookingSignal
{
    public const NAME = 'ctx_events_booking_confirmed_online';
}
