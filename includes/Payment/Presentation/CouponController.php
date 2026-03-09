<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Presentation;

use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Payment\Application\UseCases\ValidateCoupon;
use Contexis\Events\Shared\Presentation\Contracts\RestController;

final class CouponController implements RestController
{
    public function __construct(
        private ValidateCoupon $validateCoupon,
    ) {}

    public function register(): void
    {
        register_rest_route('events/v3', '/coupons/check', [
            'methods'             => 'POST',
            'callback'            => [$this, 'checkCoupon'],
            'permission_callback' => '__return_true',
            'args'                => [
                'code'          => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'event_id'      => ['required' => true, 'type' => 'integer'],
                'booking_price' => ['required' => true, 'type' => 'integer'],
                'currency'      => ['required' => false, 'type' => 'string', 'default' => 'EUR', 'sanitize_callback' => 'sanitize_text_field'],
            ],
        ]);
    }

    public function checkCoupon(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $result = $this->validateCoupon->execute(
                code: (string) $request->get_param('code'),
                eventId: EventId::from((int) $request->get_param('event_id')),
                bookingPriceCents: (int) $request->get_param('booking_price'),
                currencyCode: (string) $request->get_param('currency'),
            );

            return new \WP_REST_Response([
                'name'            => $result->name,
                'discount_type'   => $result->discountType,
                'discount_value'  => $result->discountValue,
                'discount_amount' => $result->discountAmount,
            ], 200);
        } catch (\DomainException $exception) {
            return new \WP_REST_Response(['message' => $exception->getMessage()], 422);
        }
    }
}
