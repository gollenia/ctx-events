<?php

namespace Contexis\Events\Platform\Config;

use function DI\autowire;

return [
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
	\Contexis\Events\Payment\Domain\GatewayRepository::class
    => autowire(\Contexis\Events\Payment\Infrastructure\WpGatewayRepository::class),
];