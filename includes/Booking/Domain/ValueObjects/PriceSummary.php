<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use Contexis\Events\Shared\Domain\ValueObjects\Price;

class PriceSummary
{
    public function __construct(
        public readonly Price $bookingPrice,
        public readonly Price $donationAmount,
        public readonly Price $discountAmount,
        public readonly Price $finalPrice
    ) {
    }

    public function isFree(): bool
    {
        return $this->finalPrice->isFree();
    }

    public static function fromValues(
        int $bookingPrice,
        int $donationAmount,
        int $discountAmount,
        string $currency
    ): self {
        $totalPrice = max(0, $bookingPrice + $donationAmount - $discountAmount);

        return new self(
            new Price($bookingPrice, $currency),
            new Price($donationAmount, $currency),
            new Price($discountAmount, $currency),
            new Price($totalPrice, $currency)
        );
    }
}
