<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\UseCases;

use Contexis\Events\Payment\Domain\CouponId;
use Contexis\Events\Payment\Domain\CouponRepository;

final class BulkDuplicateCoupon
{
    public function __construct(
        private readonly CouponRepository $couponRepository,
    ) {
    }

    /**
     * @return CouponId[]
     */
    public function execute(CouponId $couponId, int $count): array
    {
        if ($count < 1) {
            throw new \DomainException('Count must be at least 1.');
        }

        $coupon = $this->couponRepository->find($couponId);
        if ($coupon === null) {
            throw new \DomainException('Coupon not found.');
        }

        return $this->couponRepository->duplicateMany($couponId, $count);
    }
}
