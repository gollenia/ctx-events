<?php

namespace Contexis\Events\Location\Domain;

final class GeoCoordinates
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude
    ) {
        if (!\is_finite($this->latitude) || !\is_finite($this->longitude)) {
            throw new \InvalidArgumentException('GeoPosition: NaN/Inf are not allowed.');
        }
        if ($this->latitude < -90.0 || $this->latitude > 90.0) {
            throw new \InvalidArgumentException('Latitude must be between -90 and 90.');
        }
        if ($this->longitude < -180.0 || $this->longitude > 180.0) {
            throw new \InvalidArgumentException('Longitude must be between -180 and 180.');
        }
    }

    public static function fromFloats(float $latitude, float $longitude): self
    {
        return new self($latitude, $longitude);
    }

    public static function createOrNot(?float $latitude, ?float $longitude): ?self
    {
        if ($latitude === null || $longitude === null) {
            return null;
        }

        return new self($latitude, $longitude);
    }
}
