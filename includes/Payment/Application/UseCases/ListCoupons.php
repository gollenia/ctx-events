<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\UseCases;

use Contexis\Events\Payment\Application\Dtos\CouponCriteria;
use Contexis\Events\Payment\Application\Dtos\CouponListResponse;
use Contexis\Events\Payment\Domain\CouponRepository;

final class ListCoupons
{
    public function __construct(
        private CouponRepository $couponRepository,
    ) {
    }

    public function execute(CouponCriteria $criteria): CouponListResponse
    {
        $coupons = $this->couponRepository->findByCriteria($criteria);
        $statusCounts = $this->couponRepository->getCountsByStatus();

        return $coupons->withStatusCounts($statusCounts);
    }
}
