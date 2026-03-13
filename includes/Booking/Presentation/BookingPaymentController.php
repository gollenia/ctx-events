<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation;

use Contexis\Events\Payment\Application\UseCases\SyncTransactionStatus;
use Contexis\Events\Shared\Presentation\Contracts\RestController;

final class BookingPaymentController implements RestController
{
    public function __construct(private SyncTransactionStatus $syncTransactionStatus)
    {
    }

    public function register(): void
    {
        register_rest_route('events/v3', '/bookings/webhook', [[
            'methods' => 'POST',
            'callback' => [$this, 'handleWebhook'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => ['required' => true, 'type' => 'string'],
            ],
        ]]);
    }

    public function handleWebhook(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $this->syncTransactionStatus->execute((string) $request->get_param('id'));

            return new \WP_REST_Response(null, 204);
        } catch (\DomainException $exception) {
            return new \WP_REST_Response(['message' => $exception->getMessage()], 422);
        }
    }
}
