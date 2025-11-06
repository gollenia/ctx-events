<?php

namespace Contexis\Events\Domain\Collections;

use Contexis\Events\Domain\Models\Event;

final class EventCollection extends AbstractCollection
{
    public function __construct(Event ...$events)
    {
        $this->items = $events;
    }

    public function uniqueLocationIds(): array
    {
        $ids = array_map(
            fn(Event $event) => $event->location_id,
            $this->items
        );

        return array_values(array_unique(array_filter($ids)));
    }

    public function uniqueContactIds(): array
    {
        $ids = array_map(
            fn(Event $event) => $event->contact_id,
            $this->items
        );

        return array_values(array_unique(array_filter($ids)));
    }
}
