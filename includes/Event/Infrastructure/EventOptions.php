<?php

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Wordpress\WpOptions;

final class EventOptions extends WpOptions
{
    public const EVENT_PUBLIC_SHOW_PAST = 'ctx_events_show_past_events';
    public const EVENT_ONGOING_IS_PAST = 'ctx_events_ongoing_events_are_past';

    public function fields(): array
    {
        return [
            self::EVENT_PUBLIC_SHOW_PAST => [
                'type'        => 'bool',
                'default'     => true,
                'label'       => __('Show past events in public views', 'ctx-events'),
                'description' => __('If enabled, past events will be visible to all users in public views.', 'ctx-events'),
                'domain'      => 'events',
            ],
            self::EVENT_ONGOING_IS_PAST => [
                'type'        => 'bool',
                'default'     => false,
                'label'       => __('Consider ongoing events as past', 'ctx-events'),
                'description' => __('If enabled, events that are currently ongoing will be treated as past events.', 'ctx-events'),
                'domain'      => 'events',
            ],
        ];
    }

    public function publicShowPastEvents(): bool
    {
        return $this->getBool(self::EVENT_PUBLIC_SHOW_PAST);
    }

    public function ongoingEventsArePast(): bool
    {
        return $this->getBool(self::EVENT_ONGOING_IS_PAST);
    }
}
