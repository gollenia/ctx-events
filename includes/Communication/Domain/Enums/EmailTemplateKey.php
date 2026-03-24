<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Domain\Enums;

enum EmailTemplateKey: string
{
    case BOOKING_PENDING_MANUAL = 'booking_pending_manual';
    case BOOKING_CREATED_ONLINE = 'booking_created_online';
    case BOOKING_CONFIRMED_MANUAL = 'booking_confirmed_manual';
    case BOOKING_CONFIRMED_ONLINE = 'booking_confirmed_online';
    case BOOKING_OFFLINE_EXPIRING = 'booking_offline_expiring';
    case BOOKING_OFFLINE_EXPIRED = 'booking_offline_expired';
    case BOOKING_PAYMENT_FAILED = 'booking_payment_failed';
    case BOOKING_DENIED = 'booking_denied';
    case BOOKING_CANCELLED = 'booking_cancelled';
    case ADMIN_BOOKING_PENDING_MANUAL = 'admin_booking_pending_manual';
    case ADMIN_BOOKING_CREATED_ONLINE = 'admin_booking_created_online';
}
