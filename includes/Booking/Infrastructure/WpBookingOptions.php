<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Booking\Application\Contracts\BookingOptions;

use Contexis\Events\Shared\Infrastructure\Wordpress\WpOptions;

final class WpBookingOptions extends WpOptions implements BookingOptions
{
    public const BOOKING_ENABLED = 'ctx_events_booking_enabled';
	public const BOOKING_CURRENCY = 'ctx_events_booking_currency';

    public function fields(): array
    {
        return [
            self::BOOKING_ENABLED => [
                'type'        => 'bool',
                'default'     => true,
                'label'       => __('Enable bookings', 'ctx-events'),
                'description' => __('Enable bookings for all events.', 'ctx-events'),
                'domain'      => 'booking',
            ],
			self::BOOKING_CURRENCY => [
				'type'        => 'select',
				'default'     => 'CHF',
				'options'     => [
					'EUR' => 'EUR',
					'CHF' => 'CHF',
					'USD' => 'USD',
					'GBP' => 'GBP',
					'JPY' => 'JPY'
				],
				'label'       => __('Booking currency', 'ctx-events'),
				'description' => __('Currency used for bookings.', 'ctx-events'),
				'domain'      => 'booking',
			]
        ];
    }

    public function enabled(): bool
    {
        return $this->getBool(self::BOOKING_ENABLED);
    }

	public function currency(): string
	{
		// This could be extended to allow setting a custom currency in the future
		return $this->getString(self::BOOKING_CURRENCY);
	}
}
