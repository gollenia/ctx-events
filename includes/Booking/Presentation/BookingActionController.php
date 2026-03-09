<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation;

use Contexis\Events\Booking\Application\UseCases\ApproveBooking;
use Contexis\Events\Booking\Application\UseCases\CancelBooking;
use Contexis\Events\Booking\Application\UseCases\DeleteBooking;
use Contexis\Events\Booking\Application\UseCases\DenyBooking;
use Contexis\Events\Booking\Application\UseCases\RestoreBooking;
use Contexis\Events\Shared\Presentation\Contracts\RestController;

final class BookingActionController implements RestController
{
    use BookingControllerHelpers;

    public function __construct(
        private ApproveBooking $approveBooking,
        private DenyBooking $denyBooking,
        private CancelBooking $cancelBooking,
        private RestoreBooking $restoreBooking,
        private DeleteBooking $deleteBooking,
    ) {
    }

    public function register(): void
    {
        $baseArgs        = $this->getBaseArgs();
        $baseWithSendmail = array_merge($baseArgs, [
            'sendmail' => ['required' => false, 'type' => 'boolean', 'default' => true],
        ]);

        register_rest_route('events/v3', '/bookings/(?P<uuid>[A-Za-z0-9]{12})/approve', [[
            'methods'             => 'POST',
            'callback'            => [$this, 'approveBooking'],
            'permission_callback' => [$this, 'checkBookingAdminPermission'],
            'args'                => $baseWithSendmail,
        ]]);

        register_rest_route('events/v3', '/bookings/(?P<uuid>[A-Za-z0-9]{12})/deny', [[
            'methods'             => 'POST',
            'callback'            => [$this, 'denyBooking'],
            'permission_callback' => [$this, 'checkBookingAdminPermission'],
            'args'                => $baseWithSendmail,
        ]]);

        register_rest_route('events/v3', '/bookings/(?P<uuid>[A-Za-z0-9]{12})/cancel', [[
            'methods'             => 'POST',
            'callback'            => [$this, 'cancelBooking'],
            'permission_callback' => [$this, 'checkBookingAdminPermission'],
            'args'                => $baseWithSendmail,
        ]]);

        register_rest_route('events/v3', '/bookings/(?P<uuid>[A-Za-z0-9]{12})/restore', [[
            'methods'             => 'POST',
            'callback'            => [$this, 'restoreBooking'],
            'permission_callback' => [$this, 'checkBookingAdminPermission'],
            'args'                => $baseWithSendmail,
        ]]);

        register_rest_route('events/v3', '/bookings/(?P<uuid>[A-Za-z0-9]{12})', [[
            'methods'             => 'DELETE',
            'callback'            => [$this, 'deleteBooking'],
            'permission_callback' => [$this, 'checkBookingAdminPermission'],
            'args'                => $baseArgs,
        ]]);
    }

    private function executeAction(callable $fn, \WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $fn((string) $request->get_param('uuid'));
            return new \WP_REST_Response(null, 204);
        } catch (\DomainException $exception) {
            return new \WP_REST_Response(['message' => $exception->getMessage()], 422);
        }
    }

    public function approveBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        return $this->executeAction(fn($uuid) => $this->approveBooking->execute($uuid), $request);
    }

    public function denyBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        return $this->executeAction(fn($uuid) => $this->denyBooking->execute($uuid), $request);
    }

    public function cancelBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        return $this->executeAction(fn($uuid) => $this->cancelBooking->execute($uuid), $request);
    }

    public function restoreBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        return $this->executeAction(fn($uuid) => $this->restoreBooking->execute($uuid), $request);
    }

    public function deleteBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        return $this->executeAction(fn($uuid) => $this->deleteBooking->execute($uuid), $request);
    }
}
