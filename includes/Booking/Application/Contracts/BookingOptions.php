<?php

namespace Contexis\Events\Booking\Application\Contracts;

use Contexis\Events\Shared\Domain\Contracts\Options;
use Contexis\Events\Shared\Domain\ValueObjects\Email;

interface BookingOptions extends Options
{
    public const EXPIRATION_SYNC_MODE_WP_CRON = 'wp_cron';
    public const EXPIRATION_SYNC_MODE_EXTERNAL = 'external_cron';

    public function enabled(): bool;
	public function currency(): string;
	public function denyExpiredBookings(): bool;
    public function expirationSyncMode(): string;
    public function externalExpirationSyncToken(): string;
    public function adminNotificationEmail(): ?Email;
}
