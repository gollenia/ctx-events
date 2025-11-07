<?php

namespace Contexis\Events\Domain\Models;

use Contexis\Events\Domain\ValueObjects\Address;
use Contexis\Events\Domain\ValueObjects\GeoCoordinates;
use Contexis\Events\Domain\ValueObjects\Id\ImageId;
use Contexis\Events\Domain\ValueObjects\Id\LocationId;

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
