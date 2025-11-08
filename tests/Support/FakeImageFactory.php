<?php

namespace Tests\Support;

use Contexis\Events\Domain\ValueObjects\Image;
use Contexis\Events\Domain\ValueObjects\Id\ImageId;
use Tests\Support\Providers\ImageUrlProvider;

use function Pest\Faker\fake;

class FakeImageFactory
{
    public static function create(): Image
    {
        $faker = \Faker\Factory::create();                // Pest’s shared Faker

        return new Image(
            url: "",
            altText: fake()->word(),
            width: fake()->numberBetween(100, 2000),
            height: fake()->numberBetween(100, 2000),
            mimeType: 'image/jpeg',
            sizes: new \Contexis\Events\Domain\ValueObjects\ImageSizes([])
        );
    }
}
