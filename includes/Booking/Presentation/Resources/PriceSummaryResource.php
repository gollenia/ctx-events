<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation\Resources;

use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Contexis\Events\Shared\Presentation\Resources\PriceResource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'PriceSummary')]
final readonly class PriceSummaryResource implements Resource
{
    public function __construct(
        public PriceResource $bookingPrice,
        public PriceResource $donationAmount,
        public PriceResource $discountAmount,
        public PriceResource $finalPrice,
    ) {
    }

    public static function from(PriceSummary $priceSummary): self
    {
        return new self(
            bookingPrice: PriceResource::from($priceSummary->bookingPrice),
            donationAmount: PriceResource::from($priceSummary->donationAmount),
            discountAmount: PriceResource::from($priceSummary->discountAmount),
            finalPrice: PriceResource::from($priceSummary->finalPrice),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'bookingPrice'   => $this->bookingPrice,
            'donationAmount' => $this->donationAmount,
            'discountAmount' => $this->discountAmount,
            'finalPrice'     => $this->finalPrice,
        ];
    }
}
