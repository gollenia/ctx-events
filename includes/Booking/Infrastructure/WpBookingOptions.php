<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Booking\Application\Contracts\BookingOptions;

use Contexis\Events\Shared\Infrastructure\Wordpress\WpOptions;

final class WpBookingOptions extends WpOptions implements BookingOptions
{
    public const BOOKING_ENABLED = 'ctx_events_booking_enabled';

    public function fields(): array
    {
        return [
            self::BOOKING_ENABLED => [
                'type'        => 'bool',
                'default'     => true,
                'label'       => __('Enable bookings', 'ctx-events'),
                'description' => __('Enable bookings for all events.', 'ctx-events'),
                'domain'      => 'booking',
            ]
        ];
    }

    public function enabled(): bool
    {
        return $this->getBool(self::BOOKING_ENABLED);
    }
}
