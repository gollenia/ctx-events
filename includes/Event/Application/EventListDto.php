<?php

namespace Contexis\Events\Event\Application;

use Contexis\Events\Event\Domain\EventCollection;

class EventListDto
{
    /**
     * @param EventDto[] $events
     */
    public function __construct(
        public readonly EventCollection $events,
    ) {
        $returnvalue = [];
        foreach ($this->events as $key => $event) {
            $returnvalue[] = EventDto::fromDomainModel($event);
        }
        return $returnvalue;
    }
}
