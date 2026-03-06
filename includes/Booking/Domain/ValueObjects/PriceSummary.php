<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use Contexis\Events\Shared\Domain\ValueObjects\Currency;
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
        $finalPrice = max(0, $bookingPrice + $donationAmount - $discountAmount);

        return new self(
            new Price($bookingPrice, Currency::fromCode($currency)),
            new Price($donationAmount, Currency::fromCode($currency)),
            new Price($discountAmount, Currency::fromCode($currency)),
            new Price($finalPrice, Currency::fromCode($currency))
        );
    }

	public static function free(string $currency): self
	{
		return new self(
			new Price(0, Currency::fromCode($currency)),
			new Price(0, Currency::fromCode($currency)),
			new Price(0, Currency::fromCode($currency)),
			new Price(0, Currency::fromCode($currency))
		);
	}

	public static function fromDatabase(
		int $bookingPrice,
        int $donationAmount,
        int $discountAmount,
		int $finalPrice,
        string $currency
	): self
	{
		return new self(
			new Price($bookingPrice, Currency::fromCode($currency)),
			new Price($donationAmount, Currency::fromCode($currency)),
			new Price($discountAmount, Currency::fromCode($currency)),
			new Price($finalPrice, Currency::fromCode($currency))
		);
	}

	public function toArray(): array
	{
		return [
			'bookingPrice'   => $this->bookingPrice->toInt(),
			'donationAmount' => $this->donationAmount->toInt(),
			'discountAmount' => $this->discountAmount->toInt(),
			'finalPrice'     => $this->finalPrice->toInt(),
			'currency'        => $this->bookingPrice->currency->toString(),
		];
	} 
}
