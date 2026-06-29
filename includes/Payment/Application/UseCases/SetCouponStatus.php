<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\UseCases;

use Contexis\Events\Payment\Domain\CouponId;
use Contexis\Events\Payment\Domain\CouponRepository;
use Contexis\Events\Shared\Domain\ValueObjects\Status;

final class SetCouponStatus
{
    public function __construct(
        private readonly CouponRepository $couponRepository,
    ) {
    }

    public function execute(CouponId $couponId, Status $status): bool
    {
        $coupon = $this->couponRepository->find($couponId);
        if ($coupon === null) {
            return false;
        }

        $this->couponRepository->saveStatus($couponId, $status);
        return true;
    }
}
