<?php

namespace Contexis\Events\Location\Infrastructure;

use Contexis\Events\Location\Domain\GeoCoordinates;
use Contexis\Events\Location\Domain\Location;
use Contexis\Events\Location\Domain\LocationId;
use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Shared\Domain\ValueObjects\Address;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

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
