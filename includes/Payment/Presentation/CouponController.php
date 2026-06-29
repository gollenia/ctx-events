<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Presentation;

use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Payment\Application\Dtos\CouponCriteria;
use Contexis\Events\Payment\Application\UseCases\ExportCoupons;
use Contexis\Events\Payment\Application\UseCases\BulkDuplicateCoupon;
use Contexis\Events\Payment\Application\UseCases\DeleteCoupon;
use Contexis\Events\Payment\Application\UseCases\ListCoupons;
use Contexis\Events\Payment\Application\UseCases\SetCouponStatus;
use Contexis\Events\Payment\Application\UseCases\ValidateCoupon;
use Contexis\Events\Payment\Domain\CouponId;
use Contexis\Events\Payment\Presentation\Resources\CouponListItemResource;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;
use Contexis\Events\Shared\Infrastructure\ValueObjects\Order;
use Contexis\Events\Shared\Infrastructure\ValueObjects\OrderBy;
use Contexis\Events\Shared\Presentation\Contracts\RestController;
use Shuchkin\SimpleXLSXGen;

final class CouponController implements RestController
{
    public function __construct(
        private ListCoupons $listCoupons,
        private ExportCoupons $exportCoupons,
        private SetCouponStatus $setCouponStatus,
        private BulkDuplicateCoupon $bulkDuplicateCoupon,
        private DeleteCoupon $deleteCoupon,
        private ValidateCoupon $validateCoupon,
    ) {}

    public function register(): void
    {
        register_rest_route('events/v3', '/coupons', [
            'methods' => 'GET',
            'callback' => [$this, 'listCoupons'],
            'permission_callback' => static function () {
                return current_user_can('edit_posts');
            },
            'args' => [
                'search' => ['type' => 'string', 'required' => false],
                'page' => ['type' => 'integer', 'required' => false, 'default' => 1],
                'per_page' => ['type' => 'integer', 'required' => false, 'default' => 25],
                'order_by' => [
                    'type' => 'string',
                    'required' => false,
                    'default' => 'date',
                    'enum' => ['date', 'title', 'status'],
                ],
                'order' => [
                    'type' => 'string',
                    'required' => false,
                    'default' => 'desc',
                    'enum' => ['asc', 'desc'],
                ],
                'status' => [
                    'type' => 'string',
                    'required' => false,
                    'enum' => ['publish', 'draft', 'trash', 'future', 'private'],
                ],
            ],
        ]);

        register_rest_route('events/v3', '/coupons/export', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$this, 'exportCoupons'],
            'permission_callback' => static function () {
                return current_user_can('edit_posts');
            },
            'args' => [
                'search' => ['type' => 'string', 'required' => false],
                'order_by' => [
                    'type' => 'string',
                    'required' => false,
                    'default' => 'date',
                    'enum' => ['date', 'title', 'status'],
                ],
                'order' => [
                    'type' => 'string',
                    'required' => false,
                    'default' => 'desc',
                    'enum' => ['asc', 'desc'],
                ],
                'status' => [
                    'type' => 'string',
                    'required' => false,
                    'enum' => ['publish', 'draft', 'trash', 'future', 'private'],
                ],
            ],
        ]);

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

        register_rest_route('events/v3', '/coupons/(?P<id>\d+)/status', [
            'methods' => 'POST',
            'callback' => [$this, 'setStatus'],
            'permission_callback' => static function () {
                return current_user_can('edit_posts');
            },
            'args' => [
                'status' => [
                    'type' => 'string',
                    'enum' => ['publish', 'draft', 'trash', 'future', 'private'],
                    'required' => true,
                ],
            ],
        ]);

        register_rest_route('events/v3', '/coupons/(?P<id>\d+)/bulk-duplicate', [
            'methods' => 'POST',
            'callback' => [$this, 'bulkDuplicate'],
            'permission_callback' => static function () {
                return current_user_can('edit_posts');
            },
            'args' => [
                'count' => ['type' => 'integer', 'required' => true],
            ],
        ]);

        register_rest_route('events/v3', '/coupons/(?P<id>\d+)', [
            'methods' => \WP_REST_Server::DELETABLE,
            'callback' => [$this, 'delete'],
            'permission_callback' => static function () {
                return current_user_can('edit_posts');
            },
        ]);
    }

    public function listCoupons(\WP_REST_Request $request): \WP_REST_Response
    {
        $criteria = $this->mapCriteria($request);

        $page = $this->listCoupons->execute($criteria);
        $items = array_map(
            static fn ($item) => CouponListItemResource::fromDTO($item),
            $page->toArray(),
        );

        $response = new \WP_REST_Response($items, 200);
        $response->header('X-WP-Total', (string) $page->pagination()->totalItems);
        $response->header('X-WP-TotalPages', (string) $page->pagination()->totalPages());

        if ($page->hasStatusCounts()) {
            $response->header('X-WP-StatusCounts', (string) json_encode($page->statusCounts()?->toArray()));
        }

        return $response;
    }

    public function exportCoupons(\WP_REST_Request $request): \WP_REST_Response
    {
        $export = $this->exportCoupons->execute($this->mapCriteria($request));

        $sheets = $export->sheets;
        $firstSheet = array_shift($sheets);

        if ($firstSheet === null) {
            return new \WP_REST_Response(['message' => 'Export contains no sheets.'], 500);
        }

        $xlsx = SimpleXLSXGen::fromArray($firstSheet->rows, $firstSheet->name);
        $this->applyExportSheetOptions($xlsx, $firstSheet->rows);

        foreach ($sheets as $sheet) {
            $xlsx->addSheet($sheet->rows, $sheet->name);
            $this->applyExportSheetOptions($xlsx, $sheet->rows);
        }

        $xlsx->downloadAs(sanitize_file_name($export->fileName . '.xlsx'));
        exit;
    }

    private function mapCriteria(\WP_REST_Request $request): CouponCriteria
    {
        $statusParam = $request->get_param('status');

        return new CouponCriteria(
            orderBy: OrderBy::fromField(
                (string) ($request->get_param('order_by') ?? 'date'),
                Order::from((string) ($request->get_param('order') ?? 'desc')),
            ),
            search: $request->get_param('search'),
            page: (int) $request->get_param('page'),
            perPage: (int) $request->get_param('per_page'),
            status: is_string($statusParam) && $statusParam !== ''
                ? StatusList::fromStrings([$statusParam])
                : null,
        );
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

    public function setStatus(\WP_REST_Request $request): \WP_REST_Response
    {
        $updated = $this->setCouponStatus->execute(
            CouponId::from((int) $request->get_param('id')),
            Status::from((string) $request->get_param('status')),
        );

        if (!$updated) {
            return new \WP_REST_Response(['message' => 'Coupon not found'], 404);
        }

        return new \WP_REST_Response(['message' => 'Status updated'], 200);
    }

    public function bulkDuplicate(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $ids = $this->bulkDuplicateCoupon->execute(
                CouponId::from((int) $request->get_param('id')),
                (int) $request->get_param('count'),
            );
        } catch (\DomainException|\RuntimeException $exception) {
            return new \WP_REST_Response(['message' => $exception->getMessage()], 422);
        }

        return new \WP_REST_Response([
            'message' => 'Coupons duplicated',
            'ids' => array_map(static fn (CouponId $id): int => $id->toInt(), $ids),
        ], 200);
    }

    public function delete(\WP_REST_Request $request): \WP_REST_Response
    {
        $deleted = $this->deleteCoupon->execute(CouponId::from((int) $request->get_param('id')));

        return new \WP_REST_Response([
            'message' => $deleted ? 'Coupon deleted' : 'Coupon not found',
        ], $deleted ? 200 : 404);
    }

    /**
     * @param array<int, array<int, mixed>> $rows
     */
    private function applyExportSheetOptions(SimpleXLSXGen $xlsx, array $rows): void
    {
        if ($rows === [] || !isset($rows[0]) || !is_array($rows[0]) || count($rows[0]) === 0) {
            return;
        }

        $lastColumn = $this->spreadsheetColumnName(count($rows[0]));
        $lastRow = max(1, count($rows));

        $xlsx
            ->autoFilter(sprintf('A1:%s%d', $lastColumn, $lastRow))
            ->freezePanes('A2');
    }

    private function spreadsheetColumnName(int $columnNumber): string
    {
        $name = '';

        while ($columnNumber > 0) {
            $columnNumber--;
            $name = chr(65 + ($columnNumber % 26)) . $name;
            $columnNumber = intdiv($columnNumber, 26);
        }

        return $name;
    }
}
