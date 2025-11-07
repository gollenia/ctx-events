<?php

namespace Tests\Support;

use Contexis\Events\Domain\Models\Location;
use Contexis\Events\Domain\ValueObjects\Address;
use Contexis\Events\Domain\ValueObjects\GeoCoordinates;
use Contexis\Events\Domain\ValueObjects\Id\LocationId;

use function Pest\Faker\fake;

final class FakeLocationFactory
{
    public static function create(): Location
    {
        return new Location(
            id: LocationId::from(fake()->numberBetween(1, 1000)),
            name: fake()->word(),
            address: Address::createOrNot(
                streetAddress: fake()->address(),
                extendedAddress: fake()->secondaryAddress(),
                addressLocality: fake()->city(),
                postalCode: fake()->postcode(),
                addressRegion: fake()->state(),
                addressCountry: fake()->country()
            ),
            geoCoordinates: GeoCoordinates::createOrNot(
                latitude: fake()->randomFloat(6, -90, 90),
                longitude: fake()->randomFloat(6, -180, 180),
            ),
            imageId: null,
            externalUrl: fake()->url()
        );
    }
}
