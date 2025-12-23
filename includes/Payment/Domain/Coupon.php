<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use DateTimeImmutable;

final class Coupon
{
    public function __construct(
        public readonly CouponId $id,
        private readonly Status $status,
        private readonly CouponCode $code,
        public readonly string $name,
        private readonly DiscountType $discountType,
        public readonly int $value,
        private readonly ?DateTimeImmutable $validFrom,
        private readonly ?DateTimeImmutable $expiresAt,
        private readonly ?int $usageLimit,
        private readonly ?int $usageCount,
        public readonly ?string $description,
    ) {
    }

    public function applyTo(Price $price): Price
    {
        $minus = $this->discountType->isPercentage()
            ? $price->percentageOf($this->value)
            : $this->value;
        return $price->minus($minus);
    }

    public function isUsableAt(DateTimeImmutable $now): bool
    {
        if ($this->status !== Status::Published) {
            return false;
        }

        if ($this->isExpiredAt($now) || $this->isNotStartedAt($now)) {
            return false;
        }

        if ($this->getRemainingUses() <= 0) {
            return false;
        }

        return true;
    }

    public function isExpiredAt(DateTimeImmutable $now): bool
    {
        return $this->expiresAt && $now > $this->expiresAt;
    }

    public function isNotStartedAt(DateTimeImmutable $now): bool
    {
        return $this->validFrom && $now < $this->validFrom;
    }

    public function getRemainingUses(): int
    {
        if ($this->usageLimit === null) {
            return PHP_INT_MAX;
        }

        if ($this->usageCount === null) {
            return $this->usageLimit;
        }

        return $this->usageLimit - $this->usageCount;
    }

    public function checkAgainst(CouponCode $couponCode): bool
    {
        return $this->code->equals($couponCode);
    }

    public function getCode(): CouponCode
    {
        return $this->code;
    }
}
