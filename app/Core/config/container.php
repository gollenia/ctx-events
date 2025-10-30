<?php

use function DI\autowire;
use function DI\create;
use function DI\get;

use Contexis\Events\Domain\Repositories\EventRepository;
use Contexis\Events\Presentation\REST\EventController;
use Contexis\Events\Presentation\REST\RestRegistrar;

return [
    EventController::class => autowire(EventController::class),

    RestRegistrar::class   => create(RestRegistrar::class)
        ->constructor(get(EventController::class)),

	EventRepository::class => autowire(\Contexis\Events\Infrastructure\Persistence\WpEventRepository::class)
];