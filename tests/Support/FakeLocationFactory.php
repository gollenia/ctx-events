<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Location\Domain\Location;
use Contexis\Events\Shared\Domain\ValueObjects\Address;
use Contexis\Events\Location\Domain\GeoCoordinates;
use Contexis\Events\Location\Domain\LocationId;
use Contexis\Events\Shared\Domain\ValueObjects\Status;

use function Pest\Faker\fake;

final class FakeLocationFactory
{
    public static function create(): Location
    {
        return new Location(
            id: LocationId::from(fake()->numberBetween(1, 1000)),
            status: Status::Published,
            name: fake()->word(),
            address: new Address(
                streetAddress: fake()->address(),
                extendedAddress: fake()->secondaryAddress(),
                addressLocality: fake()->city(),
                postalCode: fake()->postcode(),
                addressRegion: fake()->state(),
                addressCountry: fake()->country()
            ),
            geoCoordinates: new GeoCoordinates(
                latitude: fake()->randomFloat(6, -90, 90),
                longitude: fake()->randomFloat(6, -180, 180),
            ),
            imageId: null,
            externalUrl: fake()->url()
        );
    }
}
