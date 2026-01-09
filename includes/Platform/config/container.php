<?php

declare(strict_types=1);

use Contexis\Events\Platform\Demo\DumpSubscriber;
use Contexis\Events\Shared\Domain\Contracts\SignalDispatcher;
use Contexis\Events\Shared\Infrastructure\Wordpress\WordpressSignalDispatcher;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

use function DI\autowire;
use function DI\create;
use function DI\get;

return [

    \Contexis\Events\Shared\Domain\Contracts\Clock::class
    => autowire(\Contexis\Events\Shared\Infrastructure\Wordpress\SystemClock::class),

	\Contexis\Events\Event\Application\Contracts\EventOptions::class
    => autowire(\Contexis\Events\Event\Infrastructure\WpEventOptions::class),

	\Contexis\Events\Booking\Application\Contracts\BookingOptions::class
    => autowire(\Contexis\Events\Booking\Infrastructure\WpBookingOptions::class),

	SignalDispatcher::class => autowire(WordpressSignalDispatcher::class),

	EventDispatcherInterface::class => function (ContainerInterface $c) {
        $dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addSubscriber(new \Contexis\Events\Event\Application\Subscribers\EventSpaceSubscriber(
			$c->get(\Contexis\Events\Event\Domain\EventRepository::class)
		));
        return $dispatcher;
    },

    \Contexis\Events\Event\Presentation\EventController::class => autowire(),

    \Contexis\Events\Platform\Wordpress\PostTypeRegistrar::class => create()
        ->constructor([
            get(\Contexis\Events\Event\Infrastructure\EventPost::class),
            get(\Contexis\Events\Location\Infrastructure\LocationPost::class),
            get(\Contexis\Events\Person\Infrastructure\PersonPost::class),
            get(\Contexis\Events\Payment\Infrastructure\CouponPost::class),
            get(\Contexis\Events\Form\Infrastructure\BookingFormPost::class),
            get(\Contexis\Events\Form\Infrastructure\AttendeeFormPost::class),
        ]),

    \Contexis\Events\Platform\Wordpress\RestRegistrar::class   => autowire()
        ->constructor([
            get(\Contexis\Events\Event\Presentation\EventController::class),
            get(\Contexis\Events\Location\Presentation\LocationController::class),
            get(\Contexis\Events\Person\Presentation\PersonController::class),
            get(\Contexis\Events\Shared\Presentation\OptionController::class),
        ]),

    \Contexis\Events\Platform\Wordpress\DatabaseMigration::class => create()
        ->constructor([
            get(\Contexis\Events\Booking\Infrastructure\BookingMigration::class),
            get(\Contexis\Events\Payment\Infrastructure\TransactionMigration::class),
            get(\Contexis\Events\Booking\Infrastructure\AttendeeMigration::class)
        ]
	),

    \Contexis\Events\Platform\Wordpress\OptionsMigration::class => create()
        ->constructor([
            get(Contexis\Events\Event\Application\Contracts\EventOptions::class),
			get(Contexis\Events\Booking\Application\Contracts\BookingOptions::class),
		]),

    \Contexis\Events\Platform\Wordpress\AdminRegistrar::class => create()
        ->constructor([
            get(\Contexis\Events\Form\Presentation\FormAdmin::class),
        ]),

    \Contexis\Events\Event\Domain\EventRepository::class
    => autowire(\Contexis\Events\Event\Infrastructure\WpEventRepository::class),
    \Contexis\Events\Location\Domain\LocationRepository::class
    => autowire(\Contexis\Events\Location\Infrastructure\WpLocationRepository::class),
    \Contexis\Events\Person\Domain\PersonRepository::class
    => autowire(\Contexis\Events\Person\Infrastructure\WpPersonRepository::class),
    \Contexis\Events\Media\Domain\ImageRepository::class
    => autowire(\Contexis\Events\Media\Infrastructure\WpImageRepository::class),
	\Contexis\Events\Form\Domain\FormRepository::class
    => autowire(\Contexis\Events\Form\Infrastructure\WpFormRepository::class),

   
];
