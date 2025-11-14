<?php

namespace Contexis\Events\Shared\Domain\ValueObjects;

final class Address implements \JsonSerializable
{
    public function __construct(
        public readonly ?string $streetAddress,
        public readonly ?string $addressLocality,
        public readonly ?string $extendedAddress,
        public readonly ?string $addressRegion,
        public readonly ?string $postalCode,
        public readonly ?string $addressCountry
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

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
