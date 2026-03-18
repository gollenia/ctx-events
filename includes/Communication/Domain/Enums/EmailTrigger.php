<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Domain\Enums;

enum EmailTrigger: string
{
    case BOOKING_PENDING = 'booking_pending';
    case BOOKING_CONFIRMED = 'booking_confirmed';
    case BOOKING_CANCELLED = 'booking_cancelled';
    case BOOKING_REMINDER = 'booking_reminder';
    case BOOKING_PAYMENT_RECEIVED = 'booking_payment_received';
    case BOOKING_PAYMENT_FAILED = 'booking_payment_failed';
}
