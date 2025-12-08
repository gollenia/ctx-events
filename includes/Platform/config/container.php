<?php
declare(strict_types=1);

use function DI\autowire;
use function DI\create;
use function DI\get;

return [

    \Contexis\Events\Shared\Infrastructure\Contracts\Clock::class
        => autowire(\Contexis\Events\Shared\Infrastructure\Wordpress\SystemClock::class),

    \Contexis\Events\Event\Presentation\EventController::class => autowire(),

    \Contexis\Events\Platform\Wordpress\PostTypeRegistrar::class => create()
        ->constructor([
            \Contexis\Events\Event\Infrastructure\EventPost::class,
            \Contexis\Events\Location\Infrastructure\LocationPost::class,
            \Contexis\Events\Person\Infrastructure\PersonPost::class
        ]),

    \Contexis\Events\Platform\Wordpress\RestRegistrar::class   => create()
        ->constructor([
            get(\Contexis\Events\Event\Presentation\EventController::class),
            get(\Contexis\Events\Location\Presentation\LocationController::class),
            get(\Contexis\Events\Person\Presentation\PersonController::class),
            get(\Contexis\Events\Shared\Presentation\OptionController::class),
        ]),

    \Contexis\Events\Platform\Wordpress\DatabaseRegistrar::class => create()
        ->constructor([
            get(\Contexis\Events\Booking\Infrastructure\BookingMigration::class),
            get(\Contexis\Events\Payment\Infrastructure\TransactionMigration::class),
            get(\Contexis\Events\Booking\Infrastructure\AttendeeMigration::class)
        ]),

    \Contexis\Events\Platform\Wordpress\OptionsRegistrar::class => create()
        ->constructor([
            get(\Contexis\Events\Event\Infrastructure\EventOptions::class),
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
