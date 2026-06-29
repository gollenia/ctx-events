<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\Dtos;

use Contexis\Events\Payment\Domain\CouponId;
use Contexis\Events\Payment\Domain\DiscountType;
use Contexis\Events\Shared\Domain\ValueObjects\Status;

final readonly class CouponListItem
{
    public function __construct(
        public CouponId $id,
        public string $title,
        public string $code,
        public DiscountType $discountType,
        public int $discountValue,
        public ?\DateTimeImmutable $validFrom,
        public ?\DateTimeImmutable $expiresAt,
        public ?int $usageLimit,
        public ?int $usageCount,
        public bool $isGlobal,
        public Status $status,
    ) {
    }
}
