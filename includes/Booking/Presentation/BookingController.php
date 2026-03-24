<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation;

use Contexis\Events\Booking\Application\DTOs\BookingListRequest;
use Contexis\Events\Booking\Application\DTOs\CreateBookingRequest;
use Contexis\Events\Booking\Application\DTOs\UpdateBookingRequest;
use Contexis\Events\Booking\Application\UseCases\CreateBooking;
use Contexis\Events\Booking\Application\UseCases\EditBooking;
use Contexis\Events\Booking\Application\UseCases\ExportEventBookings;
use Contexis\Events\Booking\Application\UseCases\ListBookings;
use Contexis\Events\Booking\Application\UseCases\ResolveBookingPaymentLink;
use Contexis\Events\Booking\Application\UseCases\UpdateBooking;
use Contexis\Events\Booking\Presentation\Resources\BookingDetailResource;
use Contexis\Events\Booking\Presentation\Resources\BookingCreatedResource;
use Contexis\Events\Booking\Presentation\Resources\BookingListItemResource;
use Contexis\Events\Booking\Presentation\Resources\BookingTransactionResource;
use Contexis\Events\Booking\Presentation\Resources\EditBookingResource;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Shared\Presentation\Contracts\RestController;
use Shuchkin\SimpleXLSXGen;

final class BookingController implements RestController
{
    use BookingControllerHelpers;

    public function __construct(
        private CreateBooking $createBooking,
        private ListBookings $listBookings,
        private EditBooking $editBooking,
        private UpdateBooking $updateBooking,
        private ExportEventBookings $exportEventBookings,
        private ResolveBookingPaymentLink $resolveBookingPaymentLink,
    ) {
    }

    public function register(): void
    {
        $baseArgs = $this->getBaseArgs();

        register_rest_route('events/v3', '/bookings', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'listBookings'],
                'permission_callback' => [$this, 'checkBookingAdminPermission'],
                'args'                => [
                    'event_id' => ['type' => 'integer', 'required' => false],
                    'status'   => ['type' => 'array', 'required' => false, 'items' => ['type' => 'integer']],
                    'search'   => ['type' => 'string', 'required' => false],
                    'gateway'  => ['type' => 'string', 'required' => false],
                    'page'     => ['type' => 'integer', 'required' => false, 'default' => 1],
                    'per_page' => ['type' => 'integer', 'required' => false, 'default' => 25],
                    'order_by' => [
                        'type'     => 'string',
                        'required' => false,
                        'default'  => 'date',
                        'enum'     => ['date', 'status', 'event_id'],
                    ],
                    'order'    => [
                        'type'     => 'string',
                        'required' => false,
                        'default'  => 'desc',
                        'enum'     => ['asc', 'desc'],
                    ],
                ],
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
                    'coupon_code'     => ['required' => false, 'type' => 'string'],
                    'donation_amount' => ['required' => false, 'type' => 'integer'],
                ],
            ],
        ]);

        register_rest_route('events/v3', '/bookings/export', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'downloadEventBookingsExport'],
                'permission_callback' => [$this, 'checkBookingAdminPermission'],
                'args'                => [
                    'event_id' => ['type' => 'integer', 'required' => true],
                    'include_attendees' => ['type' => 'boolean', 'required' => false, 'default' => false],
                ],
            ],
        ]);

        register_rest_route('events/v3', '/bookings/(?P<uuid>[A-Za-z0-9-]{6,64})', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'editBooking'],
                'permission_callback' => '__return_true',
                'args'                => $baseArgs,
            ],
            [
                'methods'             => 'PUT',
                'callback'            => [$this, 'updateBooking'],
                'permission_callback' => [$this, 'checkBookingAdminPermission'],
                'args'                => array_merge($baseArgs, [
                    'registration'  => ['required' => true, 'type' => 'object'],
                    'attendees'     => ['required' => true, 'type' => 'array'],
                    'gateway'       => ['required' => false, 'type' => 'string'],
                    'donation_cents' => ['required' => false, 'type' => 'integer', 'default' => 0],
                ]),
            ],
        ]);

        register_rest_route('events/v3', '/bookings/(?P<uuid>[A-Za-z0-9-]{6,64})/payment-link', [
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'resolvePaymentLink'],
                'permission_callback' => [$this, 'checkBookingAdminPermission'],
                'args'                => $baseArgs,
            ],
        ]);

    }

    public function listBookings(\WP_REST_Request $request): \WP_REST_Response
    {
        $eventIdParam = $request->get_param('event_id');
        $statusParam  = $request->get_param('status');

        $query = new BookingListRequest(
            eventId: $eventIdParam !== null ? EventId::from((int) $eventIdParam) : null,
            status: is_array($statusParam) ? array_map('intval', $statusParam) : null,
            search: $request->get_param('search'),
            gateway: $request->get_param('gateway'),
            page: (int) $request->get_param('page'),
            perPage: (int) $request->get_param('per_page'),
            orderBy: (string) $request->get_param('order_by'),
            order: (string) $request->get_param('order'),
        );

        $result = $this->listBookings->execute($query);

        $items = array_map(
            static fn ($item) => BookingListItemResource::fromDTO($item),
            $result->toArray()
        );

        $response = new \WP_REST_Response($items, 200);
        $response->header('X-WP-Total', (string) $result->pagination()->totalItems);
        $response->header('X-WP-TotalPages', (string) $result->pagination()->totalPages());

        if ($result->hasStatusCounts()) {
            $response->header('X-WP-StatusCounts', (string) json_encode($result->statusCounts()?->toArray()));
        }

        return $response;
    }

    public function editBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        $userContext = \Contexis\Events\Shared\Infrastructure\Wordpress\UserContextFactory::createFromCurrentUser();
        $detail = $this->editBooking->execute((string) $request->get_param('uuid'), $userContext);

        return new \WP_REST_Response(EditBookingResource::fromDTO($detail), 200);
    }

    public function createBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        $params = $request->get_params();

        $bookingRequest = new CreateBookingRequest(
            eventId: EventId::from((int) $params['event_id']),
            registration: (array) ($params['registration'] ?? []),
            attendees: (array) ($params['attendees'] ?? []),
            gateway: (string) ($params['gateway'] ?? ''),
            couponCode: isset($params['coupon_code']) ? (string) $params['coupon_code'] : null,
            donationAmount: isset($params['donation_amount']) ? (int) $params['donation_amount'] : 0,
            token: (string) ($params['token'] ?? ''),
        );

        try {
            $response = $this->createBooking->execute($bookingRequest);

            return new \WP_REST_Response(
                BookingCreatedResource::from(
                    $response->reference->toString(),
                    $response->transaction,
                    $response->emailResult,
                ),
                201
            );
        } catch (\DomainException $exception) {
            return new \WP_REST_Response(['message' => $exception->getMessage()], 422);
        }
    }

    public function updateBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        $updateRequest = new UpdateBookingRequest(
            uuid: (string) $request->get_param('uuid'),
            registration: (array) $request->get_param('registration'),
            attendees: (array) $request->get_param('attendees'),
            donationCents: (int) $request->get_param('donation_cents'),
            gateway: $request->get_param('gateway') !== null
                ? (string) $request->get_param('gateway')
                : null,
        );

        try {
            $this->updateBooking->execute($updateRequest);
            return new \WP_REST_Response(null, 204);
        } catch (\DomainException $exception) {
            return new \WP_REST_Response(['message' => $exception->getMessage()], 422);
        }
    }

    public function resolvePaymentLink(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $transaction = $this->resolveBookingPaymentLink->execute((string) $request->get_param('uuid'));

            return new \WP_REST_Response(BookingTransactionResource::fromTransaction($transaction), 200);
        } catch (\DomainException $exception) {
            return new \WP_REST_Response(['message' => $exception->getMessage()], 422);
        }
    }

    public function downloadEventBookingsExport(\WP_REST_Request $request): \WP_REST_Response
    {
        $eventId = EventId::from((int) $request->get_param('event_id'));

        if ($eventId === null) {
            return new \WP_REST_Response(['message' => 'Invalid event ID.'], 400);
        }

        try {
            $export = $this->exportEventBookings->execute(
                $eventId,
                (bool) $request->get_param('include_attendees'),
            );
        } catch (\DomainException $exception) {
            return new \WP_REST_Response(['message' => $exception->getMessage()], 404);
        }

        $sheets = $export->sheets;
        $firstSheet = array_shift($sheets);

        if ($firstSheet === null) {
            return new \WP_REST_Response(['message' => 'Export contains no sheets.'], 500);
        }

        $xlsx = SimpleXLSXGen::fromArray($firstSheet->rows, $firstSheet->name);

        foreach ($sheets as $sheet) {
            $xlsx->addSheet($sheet->rows, $sheet->name);
        }

        $xlsx->downloadAs(sanitize_file_name($export->fileName . '.xlsx'));
        exit;
    }
}
