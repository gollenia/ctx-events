<?php

use Contexis\Events\Platform\EventDispatcherFactory;
use Psr\EventDispatcher\EventDispatcherInterface;

use function DI\create;
use function DI\factory;

$subscribers = [
    \Contexis\Events\Event\Application\Subscribers\EventSpaceSubscriber::class,
];

return [
    EventDispatcherFactory::class => create()
        ->constructor($subscribers),
	EventDispatcherInterface::class => factory(EventDispatcherFactory::class),
];