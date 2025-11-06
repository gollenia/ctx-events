<?php

namespace Contexis\Events\Infrastructure\Persistence\Mappers;

use Contexis\Events\Domain\Models\Location;
use Contexis\Events\Domain\ValueObjects\Address;
use Contexis\Events\Domain\ValueObjects\Id\AttachmentId;
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
                streetAddress: $snapshot->getMetaValue('location_address'),
                extendedAddress: $snapshot->getMetaValue('location_extended_address'),
                addressLocality: $snapshot->getMetaValue('location_locality'),
                addressRegion: $snapshot->getMetaValue('location_region'),
                postalCode: $snapshot->getMetaValue('location_zip'),
                addressCountry: $snapshot->getMetaValue('location_country')
            ),
            geo: $snapshot->getMetaValue('lat') && $snapshot->getMetaValue('lng')
                ? new GeoCoordinates($snapshot->getMetaValue('lat'), $snapshot->getMetaValue('lng'))
                : null,
            attachment_id: AttachmentId::from($snapshot->getThumbnailId()),
            external_url: $snapshot->getMetaValue('external_url')
        );
    }
}
