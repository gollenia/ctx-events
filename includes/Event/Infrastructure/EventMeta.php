<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Abstracts\MetaData;

class EventMeta extends MetaData
{
    public const REGISTRATION_FORM = '_registration_form';
    public const ATTENDEE_FORM     = '_attendee_form';
    public const PERSON_ID         = '_person_id';
    public const LOCATION_ID       = '_location_id';
    public const EVENT_START       = '_event_start';
    public const EVENT_END         = '_event_end';
    public const EVENT_ALL_DAY     = '_event_all_day';
    public const BOOKING_START     = '_booking_start';
    public const BOOKING_END       = '_booking_end';
    public const BOOKING_ENABLED   = '_booking_enabled';
    public const BOOKING_SPACES    = '_booking_spaces';
    public const DONATION_ENABLED  = '_donation_enabled';
    public const RECURRENCE_ID     = '_recurrence_id';
    public const IS_DETACHED       = '_is_detached';
    public const TICKETS           = '_event_tickets';
    public const BOOKING_MAILS     = '_booking_mails';
    public const BOOKING_COUPONS   = '_booking_coupons';

    public const TICKETS_SCHEMA = [
        'schema' => [
            'type'  => 'array',
            'items' => [
                'type'       => 'object',
                'properties' => [
                    'ticket_id'          => ['type' => 'string'],
                    'ticket_name'        => ['type' => 'string'],
                    'ticket_description' => ['type' => 'string'],
                    'ticket_price'       => ['type' => 'number'],
                    'ticket_max'         => ['type' => 'integer'],
                    'ticket_min'         => ['type' => 'integer'],
                    'ticket_spaces'      => ['type' => 'integer'],
                    'ticket_start'       => ['type' => 'string'],
                    'ticket_end'         => ['type' => 'string'],
                    'ticket_active'      => ['type' => 'boolean', 'default' => true],
                    'ticket_order'       => ['type' => 'number'],
                    'ticket_form'        => ['type' => 'integer'],
                    'ticket_enabled'     => ['type' => 'boolean']
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
                    'status'  => ['type' => 'string'],
                    'subject' => ['type' => 'string'],
                    'body'    => ['type' => 'string'],
                    'enabled' => ['type' => 'boolean'],
                    'gateway' => ['type' => 'string'],
                ],
                'required' => ['status','subject','body'],
            ],
        ],
    ];

    public static array $metadata = [
        self::REGISTRATION_FORM => ['type' => 'integer'],
        self::ATTENDEE_FORM     => ['type' => 'integer'],
        self::PERSON_ID           => [
            'type'         => 'array',
            'show_in_rest' => [
                'schema' => [
                    'type'  => 'array',
                    'items' => ['type' => 'integer'],
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
        self::BOOKING_SPACES   => ['type' => 'integer'],
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

        self::BOOKING_COUPONS => [
            'type'         => 'array',
            'show_in_rest' => [
                'schema' => [
                    'type'  => 'array',
                    'items' => ['type' => 'string'], // z.B. Coupon-Codes/IDs
                ],
            ],
        ],
    ];
}
