<?php

namespace Contexis\Events\Location\Application;

use Contexis\Events\Location\Domain\GeoCoordinates;
use Contexis\Events\Location\Domain\Location;
use Contexis\Events\Media\Domain\Image;
use Contexis\Events\Shared\Domain\ValueObjects\Address;

class LocationDto
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

    public static function fromDomainModel(Location $location): self
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
