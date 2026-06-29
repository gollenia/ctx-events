<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\UseCases;

use Contexis\Events\Payment\Domain\CouponId;
use Contexis\Events\Payment\Domain\CouponRepository;

final class DeleteCoupon
{
    public function __construct(
        private readonly CouponRepository $couponRepository,
    ) {
    }

    public function execute(CouponId $couponId): bool
    {
        return $this->couponRepository->delete($couponId);
    }
}
