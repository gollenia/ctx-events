<?php

use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    \Contexis\Events\Presentation\Controllers\EventController::class => autowire(),

    \Contexis\Events\Infrastructure\PostTypes\PostTypeRegistrar::class => create()
        ->constructor([
            \Contexis\Events\Infrastructure\PostTypes\EventPost::class,
            \Contexis\Events\Infrastructure\PostTypes\LocationPost::class
        ]),

    \Contexis\Events\Presentation\Controllers\RestRegistrar::class   => create()
        ->constructor(
            get(\Contexis\Events\Presentation\Controllers\EventController::class)
        ),

    \Contexis\Events\Domain\Repositories\EventRepository::class
        => autowire(\Contexis\Events\Infrastructure\Persistence\WpEventRepository::class),
    \Contexis\Events\Domain\Repositories\LocationRepository::class
        => autowire(\Contexis\Events\Infrastructure\Persistence\WpLocationRepository::class),
    \Contexis\Events\Domain\Repositories\PersonRepository::class
        => autowire(\Contexis\Events\Infrastructure\Persistence\WpPersonRepository::class),
    \Contexis\Events\Domain\Repositories\ImageRepository::class
        => autowire(\Contexis\Events\Infrastructure\Persistence\WpImageRepository::class),
];
