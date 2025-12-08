<?php
declare(strict_types=1);

namespace Contexis\Events\Location\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Wordpress\PostStatusMapper;
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
            status: PostStatusMapper::fromPost($snapshot->post_status),
            name: $snapshot->post_title,
            address: Address::createOrNot(
                streetAddress: $snapshot->getString(LocationMeta::ADDRESS),
                extendedAddress: $snapshot->getString(LocationMeta::EXTENDED_ADDRESS),
                addressLocality: $snapshot->getString(LocationMeta::CITY),
                addressRegion: $snapshot->getString(LocationMeta::REGION),
                postalCode: $snapshot->getString(LocationMeta::POSTCODE),
                addressCountry: $snapshot->getString(LocationMeta::COUNTRY)
            ),
            geoCoordinates: $snapshot->getFloat(LocationMeta::LATITUDE) && $snapshot->getFloat(LocationMeta::LONGITUDE)
                ? new GeoCoordinates(
                    $snapshot->getFloat(LocationMeta::LATITUDE),
                    $snapshot->getFloat(LocationMeta::LONGITUDE)
                )
                : null,
            imageId: ImageId::from($snapshot->getThumbnailId()),
            externalUrl: $snapshot->getString(LocationMeta::URL)
        );
    }
}
