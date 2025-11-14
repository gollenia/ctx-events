<?php

namespace Contexis\Events\Event\Presentation;

use Contexis\Events\Event\Application\EventCriteria;
use Contexis\Events\Event\Application\ListEvents;
use Contexis\Events\Shared\Infrastructure\ViewContextFactory;
use Contexis\Events\Shared\Presentation\Contracts\RestAdapter;

class EventListController implements RestAdapter
{
    private EventCriteria $eventCriteria;


    public function __construct(
        private ListEvents $listEvents
    ) {
        $this->listEvents = $listEvents;
    }

    public function register(): void
    {
    }
}
