<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Application\Contracts\EventOptions;
use Contexis\Events\Shared\Infrastructure\Wordpress\WpOptions;

final class WpEventOptions extends WpOptions implements EventOptions
{
    public const EVENT_PUBLIC_SHOW_PAST = 'ctx_events_show_past_events';
    public const EVENT_ONGOING_IS_PAST = 'ctx_events_ongoing_events_are_past';
    public const EVENT_SLUG = 'ctx_events_event_slug';
	public const EVENT_ICON_VARIANT = 'ctx_events_icon_variant';

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
            self::EVENT_SLUG => [
                'type'        => 'string',
                'default'     => 'events',
                'label'       => __('Event slug', 'ctx-events'),
                'description' => __('The slug for the event post type. This is used in the URL to access the event (e.g. `example.com/events/concert`).', 'ctx-events'),
                'domain'      => 'events',
            ],
			self::EVENT_ICON_VARIANT => [
				'type'        => 'select',
				'default'     => 'default',
				'label'       => __('Event icon variant', 'ctx-events'),
				'description' => __('Choose the icon variant for events. This can be used to switch between different icon sets or styles.', 'ctx-events'),
				'options'     => [
					["value" => 'default', "label" => __('Internal Icons', 'ctx-events')],
					["value" => 'material', "label" => __('Material Icons', 'ctx-events')],	
				],
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

	public function getEventsSlug(): string
	{
		return $this->getString(self::EVENT_SLUG);
	}

	public function getIconVariant(): string
	{
		return $this->getString(self::EVENT_ICON_VARIANT);
	}
}
