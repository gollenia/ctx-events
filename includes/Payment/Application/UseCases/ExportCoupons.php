<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\UseCases;

use Contexis\Events\Payment\Application\Dtos\CouponCriteria;
use Contexis\Events\Payment\Application\Dtos\CouponExportData;
use Contexis\Events\Payment\Application\Dtos\CouponExportSheet;
use Contexis\Events\Payment\Domain\CouponRepository;

final class ExportCoupons
{
    public function __construct(
        private readonly CouponRepository $couponRepository,
    ) {
    }

    public function execute(CouponCriteria $criteria): CouponExportData
    {
        $coupons = $this->couponRepository->findByCriteria(
            new CouponCriteria(
                orderBy: $criteria->orderBy,
                search: $criteria->search,
                page: 1,
                perPage: 5000,
                status: $criteria->status,
            ),
        );

        $rows = [[
            __('ID', 'ctx-events'),
            __('Title', 'ctx-events'),
            __('Code', 'ctx-events'),
            __('Discount type', 'ctx-events'),
            __('Discount value', 'ctx-events'),
            __('Valid from', 'ctx-events'),
            __('Expires at', 'ctx-events'),
            __('Usage count', 'ctx-events'),
            __('Usage limit', 'ctx-events'),
            __('Global', 'ctx-events'),
            __('Status', 'ctx-events'),
        ]];

        foreach ($coupons as $coupon) {
            $rows[] = [
                $coupon->id->toInt(),
                $coupon->title,
                $coupon->code,
                $coupon->discountType->value,
                $coupon->discountValue,
                $coupon->validFrom?->format('Y-m-d H:i:s'),
                $coupon->expiresAt?->format('Y-m-d H:i:s'),
                $coupon->usageCount,
                $coupon->usageLimit,
                $coupon->isGlobal ? 'yes' : 'no',
                $coupon->status->value,
            ];
        }

        return new CouponExportData(
            fileName: 'coupons-' . date('Y-m-d'),
            sheets: [
                new CouponExportSheet(__('Coupons', 'ctx-events'), $rows),
            ],
        );
    }
}
