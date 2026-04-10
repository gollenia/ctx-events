<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use DateTimeImmutable;

final readonly class Coupon
{
    public function __construct(
        public CouponId $id,
        private Status $status,
        private CouponCode $code,
        public string $name,
        private DiscountType $discountType,
        public int $value,
        private ?DateTimeImmutable $validFrom,
        private ?DateTimeImmutable $expiresAt,
        private ?int $usageLimit,
        private ?int $usageCount,
        public ?string $description,
        public bool $isGlobal = false,
    ) {
    }

    public function getDiscountAmount(Price $price): Price
    {
        $minus = $this->discountType->isPercentage()
            ? $price->percentageOf($this->value)
            : $this->value;
        return Price::from($minus, $price->currency);
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

    public function getDiscountType(): DiscountType
    {
        return $this->discountType;
    }
}
