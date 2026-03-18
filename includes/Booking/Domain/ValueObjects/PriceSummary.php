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
		$this->validateCurrencyConsistency();
    }

    public function isFree(): bool
    {
        return $this->finalPrice->isFree();
    }

    public static function fromValues(
        Price $bookingPrice,
        Price $donationAmount,
        Price $discountAmount
    ): self {
        $finalPrice = max(0, $bookingPrice->toInt() + $donationAmount->toInt() - $discountAmount->toInt());

        return new self(
            $bookingPrice,
            $donationAmount,
            $discountAmount,
            new Price($finalPrice, $bookingPrice->currency)
        );
    }

	public static function free(): self
	{
		$currency = Currency::fromCode('EUR');
		return new self(
			new Price(0, $currency),
			new Price(0, $currency),
			new Price(0, $currency),
			new Price(0, $currency)
		);
	}

	public static function fromArray(array $data): self
	{
		$currency = Currency::fromCode($data['currency']);
		return new self(
			Price::from((int) $data['bookingPrice'], $currency),
			Price::from((int) $data['donationAmount'], $currency),
			Price::from((int) $data['discountAmount'], $currency),
			Price::from((int) $data['finalPrice'], $currency)
		);
	}

	public function withDonation(Price $donationAmount): self
	{
		return clone($this, [
			'donationAmount' => $donationAmount
		]);
	}

	private function validateCurrencyConsistency(): void
	{
		$currency = $this->bookingPrice->currency;
		if (!$this->donationAmount->currency->equals($currency) || !$this->discountAmount->currency->equals($currency) || !$this->finalPrice->currency->equals($currency)) {
			throw new \DomainException('All prices in PriceSummary must have the same currency.');
		}
	}

	/**
	 * @return array<string, int>
	 */
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
