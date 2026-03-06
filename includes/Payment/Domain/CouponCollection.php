<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class CouponCollection extends Collection
{
    protected function getItemClass(): string
    {
        return Coupon::class;
    }
}