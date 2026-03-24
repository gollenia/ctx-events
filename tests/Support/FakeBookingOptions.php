<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Booking\Application\Contracts\BookingOptions;
use Contexis\Events\Shared\Domain\ValueObjects\Email;

final class FakeBookingOptions implements BookingOptions
{
    public function __construct(
        private bool $enabled = true,
        private string $currency = 'CHF',
        private bool $denyExpiredBookings = true,
        private string $expirationSyncMode = 'wp_cron',
        private string $externalExpirationSyncToken = 'test-cron-token',
        private ?Email $adminNotificationEmail = null,
        private bool $attachIcalToBookingEmail = false,
    ) {
    }

    /** @return array<string, mixed> */
    public function fields(): array
    {
        return [];
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return [];
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function denyExpiredBookings(): bool
    {
        return $this->denyExpiredBookings;
    }

    public function expirationSyncMode(): string
    {
        return $this->expirationSyncMode;
    }

    public function externalExpirationSyncToken(): string
    {
        return $this->externalExpirationSyncToken;
    }

    public function adminNotificationEmail(): ?Email
    {
        return $this->adminNotificationEmail;
    }

    public function attachIcalToBookingEmail(): bool
    {
        return $this->attachIcalToBookingEmail;
    }
}
