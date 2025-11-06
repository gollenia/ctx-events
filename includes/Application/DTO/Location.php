<?php

namespace Contexis\Events\Application\DTO;

use Contexis\Events\Domain\ValueObjects\Address;
use Contexis\Events\Domain\ValueObjects\Attachment;
use Contexis\Events\Domain\ValueObjects\GeoCoordinates;

class Location
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly Address $address,
        public readonly ?GeoCoordinates $geo,
        public readonly ?Attachment $logo,
        public readonly ?string $external_url
    ) {
    }

    public static function fromDomainModel(\Contexis\Events\Domain\Models\Location $location): self
    {
        return new self(
            id: $location->id->toInt(),
            name: $location->name,
            address: $location->address,
            geo: $location->geo,
            logo: null,
            external_url: $location->external_url
        );
    }
}
