<?php
declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

enum DiscountType: string
{
    case PERCENTAGE = 'percent';
    case FIXED = 'fixed';

    public static function fromString(string $value): static
    {
        return match ($value) {
            '', 'percentage', 'percent' => self::PERCENTAGE,
            'fixed' => self::FIXED,
            default => throw new \Exception('Invalid discount type'),
        };
    }	

	public function isPercentage(): bool
	{
		return $this === self::PERCENTAGE;
	}

	public function isFixed(): bool
	{
		return $this === self::FIXED;
	}
}
