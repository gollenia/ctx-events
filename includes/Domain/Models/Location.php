<?php

namespace Contexis\Events\Domain\Models;

use Contexis\Events\Domain\ValueObjects\Address;
use Contexis\Events\Domain\ValueObjects\GeoCoordinates;
use Contexis\Events\Domain\ValueObjects\Id\ImageId;
use Contexis\Events\Domain\ValueObjects\Id\LocationId;
use Mpdf\Gif\Image;

final class Location
{
    public function __construct(
        public readonly LocationId $id,
        public readonly string $name,
        public readonly ?Address $address,
        public readonly ?GeoCoordinates $geo,
        public readonly ?ImageId $attachment_id,
        public readonly ?string $external_url
    ) {
    }
}
