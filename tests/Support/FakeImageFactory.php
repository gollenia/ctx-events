<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Media\Domain\Image;
use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Media\Domain\ImageSizes;
use Tests\Support\Providers\ImageUrlProvider;

use function Pest\Faker\fake;

class FakeImageFactory
{
    public static function create(): Image
    {
        $faker = \Faker\Factory::create();

        return new Image(
            id: ImageId::from(fake()->numberBetween(1, 1000)),
            url: "",
            altText: fake()->word(),
            width: fake()->numberBetween(100, 2000),
            height: fake()->numberBetween(100, 2000),
            mimeType: 'image/jpeg',
            sizes: new ImageSizes([])
        );
    }
}
