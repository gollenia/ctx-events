<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation;

final class BookingReturnLink
{
    public const QUERY_VAR = 'ctx_events_booking_return';

    public static function forReference(string $reference): string
    {
        return add_query_arg(
            self::QUERY_VAR,
            $reference,
            site_url('/'),
        );
    }
}
