<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\ValueObjects;

final readonly class Address
{
    public function __construct(
        public ?string $streetAddress,
        public ?string $addressLocality,
        public ?string $extendedAddress,
        public ?string $addressRegion,
        public ?string $postalCode,
        public ?string $addressCountry
    ) {
    }

    public static function createOrNot(
        ?string $streetAddress,
        ?string $extendedAddress,
        ?string $addressLocality,
        ?string $addressRegion,
        ?string $postalCode,
        ?string $addressCountry
    ): ?self {
        $address = new self(
            streetAddress: $streetAddress,
            extendedAddress: $extendedAddress,
            addressLocality: $addressLocality,
            addressRegion: $addressRegion,
            postalCode: $postalCode,
            addressCountry: $addressCountry
        );

        return $address->isEmpty() ? null : $address;
    }

    public function isEmpty(): bool
    {
        return empty($this->streetAddress)
        && empty($this->extendedAddress)
        && empty($this->addressLocality)
        && empty($this->addressRegion)
        && empty($this->postalCode)
        && empty($this->addressCountry);
    }

}
