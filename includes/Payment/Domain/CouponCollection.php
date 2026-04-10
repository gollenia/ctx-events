<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class CouponCollection extends Collection
{
    public static function from(Coupon ...$coupons): self
    {
        return new self($coupons);
    }
    protected function getItemClass(): string
    {
        return Coupon::class;
    }
}
