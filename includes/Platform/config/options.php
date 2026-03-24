<?php

namespace Contexis\Events\Platform\Config;

use Contexis\Events\Event\Application\Contracts\EventOptions;
use Contexis\Events\Booking\Application\Contracts\BookingOptions;
use Contexis\Events\Communication\Application\Contracts\EmailTemplateOverrideStore;

use function DI\get;
use function DI\autowire;


return [
    \Contexis\Events\Event\Application\Contracts\EventOptions::class
    => autowire(\Contexis\Events\Event\Infrastructure\WpEventOptions::class),

	\Contexis\Events\Booking\Application\Contracts\BookingOptions::class
    => autowire(\Contexis\Events\Booking\Infrastructure\WpBookingOptions::class),
    EmailTemplateOverrideStore::class
    => autowire(\Contexis\Events\Communication\Infrastructure\WpEmailTemplateOverrideStore::class),

	\Contexis\Events\Platform\Wordpress\OptionsMigration::class => autowire()->constructor([
		get(EventOptions::class),
		get(BookingOptions::class),
	]),
];
