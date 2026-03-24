<?php

namespace Contexis\Events\Platform\Config;

use function DI\autowire;
use function DI\get;

return [
    \Contexis\Events\Event\Infrastructure\WpEventRepository::class => autowire(),
    \Contexis\Events\Event\Application\Contracts\EventCalendarRepository::class
    => autowire(\Contexis\Events\Event\Infrastructure\WpEventCalendarRepository::class),
    \Contexis\Events\Event\Domain\EventRepository::class
    => get(\Contexis\Events\Event\Infrastructure\WpEventRepository::class),
    \Contexis\Events\Event\Domain\EventStatusRepository::class
    => get(\Contexis\Events\Event\Infrastructure\WpEventRepository::class),
    \Contexis\Events\Event\Domain\EventCacheRepository::class
    => get(\Contexis\Events\Event\Infrastructure\WpEventRepository::class),
    \Contexis\Events\Location\Domain\LocationRepository::class
    => autowire(\Contexis\Events\Location\Infrastructure\WpLocationRepository::class),
    \Contexis\Events\Person\Domain\PersonRepository::class
    => autowire(\Contexis\Events\Person\Infrastructure\WpPersonRepository::class),
    \Contexis\Events\Media\Domain\ImageRepository::class
    => autowire(\Contexis\Events\Media\Infrastructure\WpImageRepository::class),
	\Contexis\Events\Form\Domain\FormRepository::class
    => autowire(\Contexis\Events\Form\Infrastructure\WpFormRepository::class),
	\Contexis\Events\Payment\Domain\GatewayRepository::class
	=> autowire(\Contexis\Events\Payment\Infrastructure\WpGatewayRepository::class),
	\Contexis\Events\Payment\Domain\CouponRepository::class
	=> autowire(\Contexis\Events\Payment\Infrastructure\WpCouponRepository::class),
	\Contexis\Events\Booking\Domain\BookingRepository::class
	=> autowire(\Contexis\Events\Booking\Infrastructure\DbBookingRepository::class),
	\Contexis\Events\Booking\Application\Contracts\ReferenceGenerator::class
	=> autowire(\Contexis\Events\Booking\Infrastructure\BookingReferenceGenerator::class),
	\Contexis\Events\Booking\Domain\AttendeeRepository::class
	=> autowire(\Contexis\Events\Booking\Infrastructure\DbAttendeeRepository::class),
	\Contexis\Events\Booking\Domain\BookingTokenStore::class
	=> autowire(\Contexis\Events\Booking\Infrastructure\WpBookingTokenStore::class),
	\Contexis\Events\Payment\Domain\TransactionRepository::class
	=> autowire(\Contexis\Events\Payment\Infrastructure\DbTransactionRepository::class),
    \Contexis\Events\Payment\Application\Contracts\FindReconcilableTransactions::class
    => autowire(\Contexis\Events\Payment\Infrastructure\DbReconcilableTransactionFinder::class),
];
