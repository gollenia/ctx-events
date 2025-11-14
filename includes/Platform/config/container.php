<?php

use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    \Contexis\Events\Event\Presentation\EventController::class => autowire(),

    \Contexis\Events\Platform\Wordpress\PostTypeRegistrar::class => create()
        ->constructor([
            \Contexis\Events\Event\Infrastructure\EventPost::class,
            \Contexis\Events\Location\Infrastructure\LocationPost::class
        ]),

    \Contexis\Events\Platform\Wordpress\RestRegistrar::class   => create()
        ->constructor([
            get(\Contexis\Events\Event\Presentation\EventController::class)
        ]),

    \Contexis\Events\Event\Domain\EventRepository::class
        => autowire(\Contexis\Events\Event\Infrastructure\WpEventRepository::class),
    \Contexis\Events\Location\Domain\LocationRepository::class
        => autowire(\Contexis\Events\Location\Infrastructure\WpLocationRepository::class),
    \Contexis\Events\Person\Domain\PersonRepository::class
        => autowire(\Contexis\Events\Person\Infrastructure\WpPersonRepository::class),
    \Contexis\Events\Media\Domain\ImageRepository::class
        => autowire(\Contexis\Events\Media\Infrastructure\WpImageRepository::class),
];
