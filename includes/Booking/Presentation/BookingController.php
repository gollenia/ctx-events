<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation;

use Contexis\Events\Booking\Application\DTOs\CreateBookingRequest;
use Contexis\Events\Booking\Application\UseCases\CreateBooking;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Presentation\Contracts\RestController;

final class BookingController implements RestController
{
    public function __construct(private CreateBooking $createBooking) {}

    public function register(): void
    {
        $baseArgs = $this->getBaseArgs();

        register_rest_route('events/v3', '/bookings', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'listBookings'],
                'permission_callback' => [$this, 'checkBookingAdminPermission'],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'createBooking'],
                'permission_callback' => '__return_true',
                'args'                => [
                    'token'        => ['required' => true, 'type' => 'string'],
                    'event_id'     => ['required' => true, 'type' => 'integer'],
                    'registration' => ['required' => true, 'type' => 'object'],
                    'attendees'    => ['required' => true, 'type' => 'array'],
                    'gateway'      => ['required' => true, 'type' => 'string'],
                    'coupon_code'  => ['required' => false, 'type' => 'string'],
                ],
            ],
        ]);

        register_rest_route('events/v3', '/bookings/(?P<uuid>[A-Za-z0-9]{12})', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'editBooking'],
                'permission_callback' => [$this, 'checkBookingAdminPermission'],
                'args'                => $baseArgs,
            ],
            [
                'methods'             => 'PUT',
                'callback'            => [$this, 'updateBooking'],
                'permission_callback' => [$this, 'checkBookingAdminPermission'],
                'args'                => array_merge($baseArgs, [
                    'registration' => ['required' => true, 'type' => 'object'],
                    'attendees'    => ['required' => true, 'type' => 'array'],
                    'gateway'      => ['required' => true, 'type' => 'string'],
                    'coupon_code'  => ['required' => false, 'type' => 'string'],
                ]),
            ],
            [
                'methods'             => 'PATCH',
                'callback'            => [$this, 'setBookingStatus'],
                'permission_callback' => [$this, 'checkBookingAdminPermission'],
                'args'                => array_merge($baseArgs, [
                    'status' => ['required' => true, 'type' => 'string'],
                ]),
            ],
        ]);
    }

    public function listBookings(\WP_REST_Request $request): \WP_REST_Response
    {
        return new \WP_REST_Response([], 200);
    }

    public function editBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        return new \WP_REST_Response([], 200);
    }

    public function createBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        $params = $request->get_params();

        $bookingRequest = new CreateBookingRequest(
            eventId: EventId::from((int) $params['event_id']),
            registration: (array) ($params['registration'] ?? []),
            attendees: (array) ($params['attendees'] ?? []),
            gateway: (string) ($params['gateway'] ?? ''),
            coupon_code: isset($params['coupon_code']) ? (string) $params['coupon_code'] : null,
            token: (string) ($params['token'] ?? ''),
        );

        try {
            $reference = $this->createBooking->execute($bookingRequest);
            return new \WP_REST_Response(['reference' => $reference], 201);
        } catch (\DomainException $exception) {
            return new \WP_REST_Response(['message' => $exception->getMessage()], 422);
        }
    }

    public function updateBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        return new \WP_REST_Response([], 200);
    }

    public function setBookingStatus(\WP_REST_Request $request): \WP_REST_Response
    {
        return new \WP_REST_Response([], 200);
    }

    public function checkBookingAdminPermission(): bool
    {
        return current_user_can('manage_options');
    }

    public function isValidBookingUuid(string $value): bool
    {
        return preg_match('/^[A-Za-z0-9]{12}$/', $value) === 1;
    }

    private function getBaseArgs(): array
    {
        return [
            'uuid' => [
                'required'          => true,
                'type'              => 'string',
                'description'       => 'unique booking identifier',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => [$this, 'isValidBookingUuid'],
            ],
        ];
    }
}
