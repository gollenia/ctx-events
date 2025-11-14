<?php

namespace Contexis\Events\Location\Domain;

use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Shared\Domain\ValueObjects\Address;

final class Location
{
    public function __construct(
        public readonly LocationId $id,
        public readonly string $name,
        public readonly ?Address $address,
        public readonly ?GeoCoordinates $geoCoordinates,
        public readonly ?ImageId $imageId,
        public readonly ?string $externalUrl
    ) {
    }
}
