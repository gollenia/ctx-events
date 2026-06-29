<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\Dtos;

use Contexis\Events\Shared\Domain\Abstract\DtoCollection;

final readonly class CouponListResponse extends DtoCollection
{
    public static function from(CouponListItem ...$coupons): self
    {
        return new self($coupons);
    }
}
