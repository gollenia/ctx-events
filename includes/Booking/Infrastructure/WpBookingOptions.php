<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Booking\Application\Contracts\BookingOptions;

use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Infrastructure\Wordpress\WpOptions;

final class WpBookingOptions extends WpOptions implements BookingOptions
{
    public const BOOKING_ENABLED = 'ctx_events_booking_enabled';
	public const BOOKING_CURRENCY = 'ctx_events_booking_currency';
	public const BOOKING_DENY_EXPIRED = 'ctx_events_booking_deny_expired';
    public const BOOKING_EXPIRATION_SYNC_MODE = 'ctx_events_booking_expiration_sync_mode';
    public const BOOKING_EXPIRATION_SYNC_TOKEN = 'ctx_events_booking_expiration_sync_token';
    public const BOOKING_ADMIN_EMAIL = 'ctx_events_booking_admin_email';
	public const BOOKING_ATTACH_ICAL = 'ctx_events_booking_attach_ical';
	public const BOOKING_DONATION_ADVERTISEMENT = 'ctx_events_booking_donation_advertisement';

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
				'default'     => 'EUR',
				'options'     => [
					'EUR', 'CHF', 'USD', 'GBP', 'JPY', 'AUD', 'CAD', 'NZD', 'SEK', 'NOK', 'DKK', 'ZAR', 'HKD', 'SGD', 'MXN', 'BRL', 'INR', 'RUB', 'TRY', 'KRW', 'PLN'
				],
				'label'       => __('Booking currency', 'ctx-events'),
				'description' => __('Currency used for bookings.', 'ctx-events'),
				'domain'      => 'booking',
			],
				self::BOOKING_DENY_EXPIRED => [
					'type'        => 'bool',
					'default'     => true,
					'label'       => __('Deny expired bookings', 'ctx-events'),
					'description' => __('Automatically deny bookings when their pending payment transaction expires.', 'ctx-events'),
					'domain'      => 'booking',
                    'section'     => 'booking_expiration',
                    'section_label' => __('Expiration automation', 'ctx-events'),
                    'order'       => 10,
				],
                self::BOOKING_EXPIRATION_SYNC_MODE => [
                    'type'        => 'select',
                    'default'     => BookingOptions::EXPIRATION_SYNC_MODE_WP_CRON,
                    'label'       => __('Expiration check trigger', 'ctx-events'),
                    'description' => __('Use WordPress-Cron for simple setups or an external HTTPS cron from your hoster for more reliable execution.', 'ctx-events'),
                    'options'     => [
                        ['label' => __('WordPress-Cron', 'ctx-events'), 'value' => BookingOptions::EXPIRATION_SYNC_MODE_WP_CRON],
                        ['label' => __('External HTTPS cron', 'ctx-events'), 'value' => BookingOptions::EXPIRATION_SYNC_MODE_EXTERNAL],
                    ],
                    'domain'      => 'booking',
                    'section'     => 'booking_expiration',
                    'order'       => 20,
                ],
                self::BOOKING_EXPIRATION_SYNC_TOKEN => [
                    'type'        => 'string',
                    'default'     => $this->externalExpirationSyncToken(),
                    'label'       => __('External cron token', 'ctx-events'),
                    'description' => sprintf(
                        __('If you use an external cron, call %1$s every 15 minutes. Example: %2$s', 'ctx-events'),
                        $this->externalExpirationSyncUrl(),
                        $this->externalExpirationSyncExampleUrl()
                    ),
                    'domain'      => 'booking',
                    'section'     => 'booking_expiration',
                    'order'       => 30,
                ],
                self::BOOKING_ATTACH_ICAL => [
                    'type'        => 'boolean',
                    'default'     => false,
                    'label'       => __('Attach iCal to booking email', 'ctx-events'),
                    'description' => __('If enabled, an iCal file will be attached to booking confirmation emails.', 'ctx-events'),
                    'domain'      => 'booking',
                    'section'     => 'booking_notifications',
                    'section_label' => __('Notification recipients', 'ctx-events'),
                    'order'       => 35,
                ],
                self::BOOKING_ADMIN_EMAIL => [
                    'type'        => 'string',
                    'default'     => '',
                    'label'       => __('Booking admin email', 'ctx-events'),
                    'description' => __('Optional fallback email address for booking-related admin notifications.', 'ctx-events'),
                    'domain'      => 'booking',
                    'section'     => 'booking_notifications',
                    'section_label' => __('Notification recipients', 'ctx-events'),
                    'order'       => 40,
                ],
				self::BOOKING_DONATION_ADVERTISEMENT => [
					'type'        => 'string',
					'default'     => '',
					'label'       => __('Donation advertisement', 'ctx-events'),
					'description' => __('Optional HTML content to advertise donations in the booking confirmation email.', 'ctx-events'),
					'domain'      => 'booking',
					'section'     => 'booking_notifications',
					'section_label' => __('Notification recipients', 'ctx-events'),
					'order'       => 50,
				],
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

	public function denyExpiredBookings(): bool
	{
		return $this->getBool(self::BOOKING_DENY_EXPIRED);
	}

    public function expirationSyncMode(): string
    {
        $mode = $this->getString(self::BOOKING_EXPIRATION_SYNC_MODE, BookingOptions::EXPIRATION_SYNC_MODE_WP_CRON);

        return in_array($mode, [BookingOptions::EXPIRATION_SYNC_MODE_WP_CRON, BookingOptions::EXPIRATION_SYNC_MODE_EXTERNAL], true)
            ? $mode
            : BookingOptions::EXPIRATION_SYNC_MODE_WP_CRON;
    }

    public function externalExpirationSyncToken(): string
    {
        $token = $this->getString(self::BOOKING_EXPIRATION_SYNC_TOKEN, '');
        if (is_string($token) && $token !== '') {
            return $token;
        }

        $token = bin2hex(random_bytes(16));
        update_option(self::BOOKING_EXPIRATION_SYNC_TOKEN, $token);

        return $token;
    }

    public function adminNotificationEmail(): ?Email
    {
        return Email::tryFrom($this->getString(self::BOOKING_ADMIN_EMAIL, ''));
    }

	public function attachIcalToBookingEmail(): bool
	{
		return $this->getBool(self::BOOKING_ATTACH_ICAL);
	}

    private function externalExpirationSyncUrl(): string
    {
        return (string) rest_url('events/v3/payments/reconcile');
    }

    private function externalExpirationSyncExampleUrl(): string
    {
        return $this->externalExpirationSyncUrl() . '?token=' . rawurlencode($this->externalExpirationSyncToken());
    }

	public function donationAdvertisement(): ?string
	{
		$advertisement = $this->getString(self::BOOKING_DONATION_ADVERTISEMENT, '');
		return $advertisement !== '' ? $advertisement : null;
	}
}
