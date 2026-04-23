<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Abstracts\MetaData;

class EventMeta extends MetaData
{
    public const BOOKING_FORM      = '_booking_form';
    public const ATTENDEE_FORM     = '_attendee_form';
    public const PERSON_ID         = '_person_id';
    public const LOCATION_ID       = '_location_id';
    public const EVENT_START       = '_event_start';
    public const EVENT_END         = '_event_end';
    public const EVENT_ALL_DAY     = '_event_all_day';
    public const BOOKING_START     = '_booking_start';
    public const BOOKING_END       = '_booking_end';
    public const BOOKING_ENABLED   = '_booking_enabled';
    public const BOOKING_CAPACITY  = '_booking_capacity';
	public const ALLOW_COUPONS    = '_booking_allow_coupons';
	public const BOOKING_CURRENCY  = '_booking_currency';
    public const BOOKING_REFERENCE_PREFIX = '_booking_reference_prefix';
    public const BOOKING_REFERENCE_SUFFIX = '_booking_reference_suffix';
    public const DONATION_ENABLED  = '_donation_enabled';
	public const VIEW_CONFIG		  = '_view_config';
    public const RECURRENCE_ID     = '_recurrence_id';
    public const IS_DETACHED       = '_is_detached';
    public const TICKETS           = '_event_tickets';
    public const BOOKING_MAILS     = '_booking_mails';
    public const COUPONS_ALLOWED   = '_booking_coupons';
	public const COUPONS_ENABLED = '_coupons_enabled';
    public const CACHED_MIN_PRICE = '_cached_min_price';
    public const CACHED_MAX_PRICE = '_cached_max_price';
    public const GATEWAYS_EXCLUDED = '_gateways_excluded';

    public const TICKETS_SCHEMA = [
        'schema' => [
            'type'  => 'array',
            'items' => [
                'type'       => 'object',
                'properties' => [
                    'ticket_id'          => ['type' => 'string'],
                    'ticket_name'        => ['type' => 'string'],
                    'ticket_description' => ['type' => 'string'],
                    'ticket_price'       => ['type' => 'integer'],
                    'ticket_max'         => ['type' => 'integer'],
                    'ticket_min'         => ['type' => 'integer'],
                    'ticket_spaces'      => ['type' => 'integer'],
                    'ticket_start'       => ['type' => 'string'],
                    'ticket_end'         => ['type' => 'string'],
                    'ticket_order'       => ['type' => 'integer'],
                    'ticket_form'        => ['type' => 'integer'],
                    'ticket_enabled'     => ['type' => 'boolean', 'default' => true]
                ],
                'required' => ['ticket_id','ticket_name','ticket_price'],
            ],
        ],
    ];

    public const BOOKING_MAILS_SCHEMA = [
        'schema' => [
            'type'  => 'array',
            'items' => [
                'type'       => 'object',
                'properties' => [
                    'key'     => ['type' => 'string'],
                    'subject' => ['type' => ['string', 'null']],
                    'body'    => ['type' => 'string'],
                    'enabled' => ['type' => 'boolean'],
                    'replyTo' => ['type' => ['string', 'null']],
                ],
                'required' => ['key','body','enabled'],
            ],
        ],
    ];

    public static array $metadata = [
        self::BOOKING_FORM => ['type' => 'integer'],
        self::ATTENDEE_FORM     => ['type' => 'integer'],
        self::BOOKING_CURRENCY => ['type' => 'string'],
        self::BOOKING_REFERENCE_PREFIX => ['type' => 'string', 'default' => ''],
        self::BOOKING_REFERENCE_SUFFIX => ['type' => 'string', 'default' => ''],
        self::PERSON_ID           => [
            'type'              => 'integer',
            'default'           => 0,
            'sanitize_callback' => [self::class, 'sanitizeRelationId'],
            'show_in_rest'      => [
                'schema' => [
                    'type'    => ['integer', 'array', 'null'],
                    'items'   => ['type' => 'integer'],
                    'default' => 0,
                ],
            ],
        ],
        self::LOCATION_ID         => ['type' => 'integer'],
        self::EVENT_START      => ['type' => 'string'],
        self::EVENT_END        => ['type' => 'string'],
        self::EVENT_ALL_DAY    => ['type' => 'boolean'],
        self::BOOKING_START    => ['type' => 'string'],
        self::BOOKING_END      => ['type' => 'string'],
        self::BOOKING_ENABLED  => ['type' => 'boolean'],
        self::BOOKING_CAPACITY => ['type' => 'integer'],
		self::COUPONS_ENABLED => ['type' => 'boolean', 'default' => true],
		self::VIEW_CONFIG => [
            'type' => 'object',
            'show_in_rest' => [
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'showFreeSpacesThreshold' => ['type' => 'integer', 'default' => 0],
                        'showFreeSpaces' => ['type' => 'boolean', 'default' => true],
                    ],
                ],
            ],
        ],
        self::DONATION_ENABLED => ['type' => 'boolean'],
        self::RECURRENCE_ID    => ['type' => 'integer'],
        self::IS_DETACHED      => ['type' => 'boolean'],

        self::TICKETS => [
            'type'         => 'array',
            'show_in_rest' => self::TICKETS_SCHEMA,
        ],

        self::BOOKING_MAILS => [
            'type'         => 'array',
            'show_in_rest' => self::BOOKING_MAILS_SCHEMA,
        ],

        self::COUPONS_ALLOWED => [
            'type'         => 'array',
            'show_in_rest' => [
                'schema' => [
                    'type'  => 'array',
                    'items' => ['type' => 'integer'],
                ],
            ],
        ],

		self::CACHED_MIN_PRICE => ['type' => 'integer', 'readonly' => true, 'show_in_rest' => false],
		self::CACHED_MAX_PRICE => ['type' => 'integer', 'readonly' => true, 'show_in_rest' => false],
		self::GATEWAYS_EXCLUDED => [
			'type'         => 'array',
			'show_in_rest' => [
				'schema' => [
					'type'  => 'array',
					'items' => ['type' => 'string'],
				],
			],
		],
    ];

	public static function sanitizeRelationId(mixed $value): int
	{
		if (is_array($value)) {
			$value = reset($value);
		}

		return max(0, (int) $value);
	}
}
