<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Presentation;

use Contexis\Events\Booking\Application\Contracts\BookingOptions;
use Contexis\Events\Payment\Application\UseCases\ReconcilePendingTransactions;
use Contexis\Events\Shared\Presentation\Contracts\RestController;

final class PaymentReconciliationController implements RestController
{
    public function __construct(
        private ReconcilePendingTransactions $reconcilePendingTransactions,
        private BookingOptions $bookingOptions,
    ) {
    }

    public function register(): void
    {
        register_rest_route('events/v3', '/payments/reconcile', [[
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'reconcile'],
            'permission_callback' => '__return_true',
        ]]);
    }

    public function reconcile(\WP_REST_Request $request): \WP_REST_Response
    {
        if (!$this->bookingOptions->denyExpiredBookings()) {
            return new \WP_REST_Response([
                'enabled' => false,
                'message' => 'Automatic expiration is disabled.',
            ], 409);
        }

        if ($this->bookingOptions->expirationSyncMode() !== BookingOptions::EXPIRATION_SYNC_MODE_EXTERNAL) {
            return new \WP_REST_Response([
                'enabled' => false,
                'message' => 'External reconciliation is not enabled.',
            ], 409);
        }

        $providedToken = $this->extractToken($request);
        if ($providedToken === '' || !hash_equals($this->bookingOptions->externalExpirationSyncToken(), $providedToken)) {
            return new \WP_REST_Response(['message' => 'Invalid reconciliation token.'], 401);
        }

        $updated = $this->reconcilePendingTransactions->execute();

        return new \WP_REST_Response([
            'success' => true,
            'updatedTransactions' => $updated,
        ], 200);
    }

    private function extractToken(\WP_REST_Request $request): string
    {
        $header = $request->get_header('x-ctx-events-cron-token');
        if (is_string($header) && $header !== '') {
            return $header;
        }

        $token = $request->get_param('token');

        return is_string($token) ? $token : '';
    }
}
