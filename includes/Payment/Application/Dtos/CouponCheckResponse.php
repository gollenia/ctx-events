<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\DTOs;

final readonly class CouponCheckResponse
{
    public function __construct(
        public string $name,
        public string $discountType,
        public int $discountValue,
        public int $discountAmount,
    ) {}
}
