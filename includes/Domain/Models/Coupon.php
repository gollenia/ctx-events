<?php

namespace Contexis\Events\Domain\Models;

use Contexis\Events\Domain\ValueObjects\Id\CouponId;
use DateTimeImmutable;

final class Coupon
{
    public function __construct(
        public readonly CouponId $id,
        public readonly string $owner,
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?float $discount_percentage,
        public readonly ?float $discount_fixed,
        public readonly ?float $minimum_order_amount,
        public readonly ?DateTimeImmutable $valid_from,
        public readonly ?DateTimeImmutable $valid_until,
        public readonly ?int $usage_limit,
        public readonly ?int $usage_count
    ) {
    }

    public function isUsable(): bool
    {
        $now = new DateTimeImmutable();

        if ($this->valid_from && $now < $this->valid_from) {
            return false;
        }

        if ($this->valid_until && $now > $this->valid_until) {
            return false;
        }

        if ($this->usage_limit !== null && $this->usage_count !== null && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }
}
