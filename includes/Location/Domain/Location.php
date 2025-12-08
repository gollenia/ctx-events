<?php
declare(strict_types=1);

namespace Contexis\Events\Location\Domain;

use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Domain\Traits\HasStatus;
use Contexis\Events\Shared\Domain\ValueObjects\Address;

final class Location
{
    use HasStatus;

    public function __construct(
        public readonly LocationId $id,
        public readonly Status $status,
        public readonly string $name,
        public readonly ?Address $address,
        public readonly ?GeoCoordinates $geoCoordinates,
        public readonly ?ImageId $imageId,
        public readonly ?string $externalUrl
    ) {
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
