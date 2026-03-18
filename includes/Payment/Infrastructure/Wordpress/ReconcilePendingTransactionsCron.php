<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure\Wordpress;

use Contexis\Events\Booking\Application\Contracts\BookingOptions;
use Contexis\Events\Payment\Application\UseCases\ReconcilePendingTransactions;
use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;

final class ReconcilePendingTransactionsCron implements Registrar
{
    private const HOOK = 'ctx_events_payment_reconcile_pending';

    public function __construct(
        private ReconcilePendingTransactions $reconcilePendingTransactions,
        private BookingOptions $bookingOptions,
    ) {
    }

    public function hook(): void
    {
        add_action('init', [$this, 'registerSchedule']);
        add_action(self::HOOK, [$this, 'run']);
    }

    public function registerSchedule(): void
    {
        if (
            !$this->bookingOptions->denyExpiredBookings()
            || $this->bookingOptions->expirationSyncMode() !== BookingOptions::EXPIRATION_SYNC_MODE_WP_CRON
        ) {
            wp_clear_scheduled_hook(self::HOOK);

            return;
        }

        if (wp_next_scheduled(self::HOOK) !== false) {
            return;
        }

        wp_schedule_event(time() + (5 * MINUTE_IN_SECONDS), 'hourly', self::HOOK);
    }

    public function run(): void
    {
        if (
            !$this->bookingOptions->denyExpiredBookings()
            || $this->bookingOptions->expirationSyncMode() !== BookingOptions::EXPIRATION_SYNC_MODE_WP_CRON
        ) {
            return;
        }

        $this->reconcilePendingTransactions->execute();
    }
}
