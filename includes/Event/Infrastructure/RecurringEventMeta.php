<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Abstracts\MetaData;

/**
 * Metadata that describes an event series rather than an individual event.
 */
final class RecurringEventMeta extends MetaData
{
    public const LOCATION_ID = EventMeta::LOCATION_ID;
    public const PERSON_ID = EventMeta::PERSON_ID;
    public const AUDIENCE = EventMeta::AUDIENCE;
    public const FIRST_OCCURRENCE_STARTS_AT = '_recurrence_first_occurrence_starts_at';
    public const OCCURRENCE_DURATION_SECONDS = '_recurrence_occurrence_duration_seconds';
    public const TIMEZONE = '_recurrence_timezone';
    public const FREQUENCY = '_recurrence_frequency';
    public const INTERVAL = '_recurrence_interval';
    public const WEEKDAYS = '_recurrence_weekdays';
    public const MONTHDAY = '_recurrence_monthday';
    public const WEEKDAY_POSITION = '_recurrence_weekday_position';
    public const ENDS_ON = '_recurrence_ends_on';
    public const EXCEPTIONS = '_recurrence_exceptions';

    protected static array $metadata = [
        self::LOCATION_ID => [
            'type' => 'integer',
        ],
        self::PERSON_ID => [
            'type' => 'integer',
        ],
        self::AUDIENCE => [
            'type' => 'string',
        ],
        self::FIRST_OCCURRENCE_STARTS_AT => [
            'type' => 'string',
            'show_in_rest' => [
                'schema' => ['type' => 'string', 'format' => 'date-time'],
            ],
        ],
        self::OCCURRENCE_DURATION_SECONDS => [
            'type' => 'integer',
            'default' => 0,
        ],
        self::TIMEZONE => [
            'type' => 'string',
        ],
        self::FREQUENCY => [
            'type' => 'string',
        ],
        self::INTERVAL => [
            'type' => 'integer',
            'default' => 1,
        ],
        self::WEEKDAYS => [
            'type' => 'array',
            'show_in_rest' => [
                'schema' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
        ],
        self::MONTHDAY => [
            'type' => 'integer',
        ],
        self::WEEKDAY_POSITION => [
            'type' => 'integer',
        ],
        self::ENDS_ON => [
            'type' => 'string',
            'show_in_rest' => [
                'schema' => ['type' => 'string', 'format' => 'date'],
            ],
        ],
        self::EXCEPTIONS => [
            'type' => 'object',
            'default' => [],
            'show_in_rest' => [
                'schema' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        'type' => 'object',
                        'properties' => [
                            'type' => [
                                'type' => 'string',
                                'enum' => ['cancelled', 'detached'],
                            ],
                            'eventPostId' => [
                                'type' => 'integer',
                            ],
                        ],
                        'required' => ['type'],
                    ],
                ],
            ],
        ],
    ];
}
