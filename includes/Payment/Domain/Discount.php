<?php
declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

final class Discount
{
    private function __construct(
        public readonly DiscountType $type,
        public readonly float $amount,
    ) {
    }

    public static function percent(float $percent): self
    {
        return new self(DiscountType::PERCENTAGE, $percent);
    }

    public static function fixed(float $cents): self
    {
        return new self(DiscountType::FIXED, $cents);
    }

    public function applyTo(int $amountCents): int
    {
        return match ($this->type) {
            DiscountType::PERCENTAGE => max(0, (int) round($amountCents * (1 - $this->amount / 100))),
            DiscountType::FIXED   => max(0, (int) round($amountCents - $this->amount)),
            default   => $amountCents,
        };
    }
}
