<?php

namespace Contexis\Events\Infrastructure\Persistence\Mappers;

use Contexis\Events\Domain\Models\Location;
use Contexis\Events\Domain\ValueObjects\Address;
use Contexis\Events\Domain\ValueObjects\Id\ImageId;
use Contexis\Events\Domain\ValueObjects\GeoCoordinates;
use Contexis\Events\Domain\ValueObjects\Id\LocationId;
use Contexis\Events\Infrastructure\PostTypes\PostSnapshot;

class LocationMapper
{
    public static function map(PostSnapshot $snapshot): Location
    {
        return new Location(
            id: LocationId::from($snapshot->id),
            name: $snapshot->post_title,
            address: Address::createOrNot(
                streetAddress: $snapshot->getString('location_address'),
                extendedAddress: $snapshot->getString('location_extended_address'),
                addressLocality: $snapshot->getString('location_locality'),
                addressRegion: $snapshot->getString('location_region'),
                postalCode: $snapshot->getString('location_zip'),
                addressCountry: $snapshot->getString('location_country')
            ),
            geoCoordinates: $snapshot->getFloat('lat') && $snapshot->getFloat('lng')
                ? new GeoCoordinates($snapshot->getFloat('lat'), $snapshot->getFloat('lng'))
                : null,
            imageId: ImageId::from($snapshot->getThumbnailId()),
            externalUrl: $snapshot->getString('external_url')
        );
    }
}
