<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation;

use Contexis\Events\Booking\Application\Contracts\BookingAction;
use Contexis\Events\Booking\Application\DTOs\BookingActionRequest;
use Contexis\Events\Booking\Application\UseCases\ApproveBooking;
use Contexis\Events\Booking\Application\UseCases\CancelBooking;
use Contexis\Events\Booking\Application\UseCases\DeleteBooking;
use Contexis\Events\Booking\Application\UseCases\DenyBooking;
use Contexis\Events\Booking\Application\UseCases\RestoreBooking;
use Contexis\Events\Communication\Application\BookingEmailWarnings;
use Contexis\Events\Shared\Presentation\Contracts\RestController;

final class BookingActionController implements RestController
{
    use BookingControllerHelpers;

    private const string UUID_PATTERN = '[A-Za-z0-9-]{6,64}';

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
            'cancellation_reason' => ['required' => false, 'type' => 'string', 'default' => ''],
        ]);

        register_rest_route('events/v3', '/bookings/(?P<uuid>' . self::UUID_PATTERN . ')/approve', [[
            'methods'             => 'POST',
            'callback'            => [$this, 'approveBooking'],
            'permission_callback' => [$this, 'checkBookingAdminPermission'],
            'args'                => $baseWithSendmail,
        ]]);

        register_rest_route('events/v3', '/bookings/(?P<uuid>' . self::UUID_PATTERN . ')/deny', [[
            'methods'             => 'POST',
            'callback'            => [$this, 'denyBooking'],
            'permission_callback' => [$this, 'checkBookingAdminPermission'],
            'args'                => $baseWithSendmail,
        ]]);

        register_rest_route('events/v3', '/bookings/(?P<uuid>' . self::UUID_PATTERN . ')/cancel', [[
            'methods'             => 'POST',
            'callback'            => [$this, 'cancelBooking'],
            'permission_callback' => [$this, 'checkBookingAdminPermission'],
            'args'                => $baseWithSendmail,
        ]]);

        register_rest_route('events/v3', '/bookings/(?P<uuid>' . self::UUID_PATTERN . ')/restore', [[
            'methods'             => 'POST',
            'callback'            => [$this, 'restoreBooking'],
            'permission_callback' => [$this, 'checkBookingAdminPermission'],
            'args'                => $baseWithSendmail,
        ]]);

        register_rest_route('events/v3', '/bookings/(?P<uuid>' . self::UUID_PATTERN . ')', [[
            'methods'             => 'DELETE',
            'callback'            => [$this, 'deleteBooking'],
            'permission_callback' => [$this, 'checkBookingAdminPermission'],
            'args'                => $baseArgs,
        ]]);
    }

    private function executeAction(BookingAction $action, \WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $sendMail = $request->get_param('sendmail');

            $emailResult = $action->execute(new BookingActionRequest(
                reference: (string) $request->get_param('uuid'),
                sendMail: $sendMail === null ? true : (bool) $sendMail,
                cancellationReason: $this->sanitizeOptionalReason($request->get_param('cancellation_reason')),
            ));
            $warnings = BookingEmailWarnings::messages($emailResult);

            if ($warnings === []) {
                return new \WP_REST_Response(null, 204);
            }

            return new \WP_REST_Response(['warnings' => $warnings], 200);
        } catch (\DomainException $exception) {
            return new \WP_REST_Response(['message' => $exception->getMessage()], 422);
        }
    }

    public function approveBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        return $this->executeAction($this->approveBooking, $request);
    }

    public function denyBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        return $this->executeAction($this->denyBooking, $request);
    }

    public function cancelBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        return $this->executeAction($this->cancelBooking, $request);
    }

    public function restoreBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        return $this->executeAction($this->restoreBooking, $request);
    }

    public function deleteBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        return $this->executeAction($this->deleteBooking, $request);
    }

    private function sanitizeOptionalReason(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $reason = trim(sanitize_textarea_field($value));

        return $reason === '' ? null : $reason;
    }
}
