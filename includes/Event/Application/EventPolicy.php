<?php

namespace Contexis\Events\Event\Application;

use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Infrastructure\EventOptions;
use Contexis\Events\Shared\Domain\ValueObjects\ViewContext;
use Contexis\Events\Shared\Infrastructure\Contracts\Clock;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostPolicy;

final class EventPolicy
{
    public function __construct(
        private readonly EventOptions $eventOptions,
        private readonly Clock $clock
    ) {
    }

    public function canView(
        Event $event,
        ViewContext $viewContext,
    ): bool {
        if (!PostPolicy::canView($event->id->toInt())) {
            return false;
        }

        if (!$viewContext->isAnonymous()) {
            return true;
        }

        $eventIsPast = $this->eventOptions->ongoingEventsArePast() ?
            $event->isOngoing($this->clock->now()) :
            $event->isPast($this->clock->now());

        if ($eventIsPast && !$this->eventOptions->publicShowPastEvents()) {
            return false;
        }

        return true;
    }
}
