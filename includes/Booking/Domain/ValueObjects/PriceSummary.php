<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use Contexis\Events\Shared\Domain\ValueObjects\Price;

class PriceSummary
{
    public function __construct(
        public readonly Price $base_price,
        public readonly Price $donation_amount,
        public readonly Price $discount_amount,
        public readonly Price $total_price
    ) {
    }

    public function isFree(): bool
    {
        return $this->total_price->isFree();
    }

    public static function fromValues(
        int $base_price,
        int $donation_amount,
        int $discount_amount,
        string $currency
    ): self {
        $total_price = max(0, $base_price + $donation_amount - $discount_amount);

        return new self(
            new Price($base_price, $currency),
            new Price($donation_amount, $currency),
            new Price($discount_amount, $currency),
            new Price($total_price, $currency)
        );
    }
}
