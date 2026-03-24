<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Presentation;

use Contexis\Events\Payment\Application\UseCases\GetBookingPaymentQr;
use Contexis\Events\Payment\Presentation\Resources\PaymentQrResource;
use Contexis\Events\Shared\Presentation\Contracts\RestController;

final class PaymentQrController implements RestController
{
    public function __construct(
        private GetBookingPaymentQr $getBookingPaymentQr,
    ) {
    }

    public function register(): void
    {
        register_rest_route('events/v3', '/payments/bookings/(?P<uuid>[A-Za-z0-9-]{6,64})/qr', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'getBookingPaymentQr'],
                'permission_callback' => '__return_true',
                'args' => [
                    'uuid' => [
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'format' => [
                        'required' => false,
                        'type' => 'string',
                        'default' => 'svg',
                        'enum' => ['svg', 'png'],
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
        ]);
    }

    public function getBookingPaymentQr(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $response = $this->getBookingPaymentQr->execute(
                (string) $request->get_param('uuid'),
                (string) $request->get_param('format'),
            );

            return new \WP_REST_Response(
                PaymentQrResource::fromDto($response),
                200,
            );
        } catch (\DomainException $exception) {
            return new \WP_REST_Response(['message' => $exception->getMessage()], 422);
        }
    }
}
