<?php

namespace Contexis\Events\Application\DTO;

use Contexis\Events\Domain\ValueObjects\Address;
use Contexis\Events\Domain\ValueObjects\Image;
use Contexis\Events\Domain\ValueObjects\GeoCoordinates;

class Location
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly Address $address,
        public readonly ?GeoCoordinates $geoCoordinates,
        public readonly ?Image $logo,
        public readonly ?string $externalUrl
    ) {
    }

    public static function fromDomainModel(\Contexis\Events\Domain\Models\Location $location): self
    {
        return new self(
            id: $location->id->toInt(),
            name: $location->name,
            address: $location->address,
            geoCoordinates: $location->geoCoordinates,
            logo: null,
            externalUrl: $location->externalUrl
        );
    }
}
