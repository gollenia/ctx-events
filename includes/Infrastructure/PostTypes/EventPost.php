<?php

namespace Contexis\Events\Infrastructure\PostTypes;

use Contexis\Events\Collections\EventCollection;
use Contexis\Events\Core\Contracts\PostType;
use Contexis\Events\Core\Contracts\HasTaxonomy;
use Contexis\Events\Core\Contracts\HasMetaData;
use Contexis\Events\Infrastructure\Persistence\WpEventRepository;
use Contexis\Events\Application\Repositories\EventRepository;
use Contexis\Events\Core\Contracts\HasTaxonomies;
use Contexis\Events\Models\Event;
use Contexis\Events\PostTypes\RecurringEventPost;
use WP_Query;

class EventPost extends AbstractPostType implements HasTaxonomies, HasMetaData
{
    public const POST_TYPE = "ctx-event";
    public const CATEGORIES = 'ctx-event-categories';
    public const TAGS = 'ctx-event-tags';

    public static function getSlug(): string
    {
        return get_option('dbem_cp_events_slug', 'events');
    }

    public function registerTaxonomies(): void
    {
        register_taxonomy(self::POST_TYPE . '-tags', [self::POST_TYPE], [
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'label' => __('Event Tags'),
            'show_admin_column' => true,
            'singular_label' => __('Event Tag'),
            'labels' => [
                'name' => __('Event Tags', 'events'),
                'singular_name' => __('Event Tag', 'events'),
                'search_items' => __('Search Event Tags', 'events'),
                'popular_items' => __('Popular Event Tags', 'events'),
                'all_items' => __('All Event Tags', 'events'),
                'parent_items' => __('Parent Event Tags', 'events'),
                'parent_item_colon' => __('Parent Event Tag:', 'events'),
                'edit_item' => __('Edit Event Tag', 'events'),
                'update_item' => __('Update Event Tag', 'events'),
                'add_new_item' => __('Add New Event Tag', 'events'),
                'new_item_name' => __('New Event Tag Name', 'events'),
                'separate_items_with_commas' => __('Separate event tags with commas', 'events'),
                'add_or_remove_items' => __('Add or remove events', 'events'),
                'choose_from_the_most_used' => __('Choose from most used event tags', 'events'),
            ]
        ]);

        register_taxonomy(self::POST_TYPE . '-categories', [self::POST_TYPE], [
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => EventPost::POST_TYPE . '/categories', 'hierarchical' => true,'with_front' => false],
            'show_in_nav_menus' => true,
            'label' => __('Event Categories', 'events'),
            'singular_label' => __('Event Category', 'events'),
            'labels' => [
                'name' => __('Event Categories', 'events'),
                'singular_name' => __('Event Category', 'events'),
                'search_items' => __('Search Event Categories', 'events'),
                'popular_items' => __('Popular Event Categories', 'events'),
                'all_items' => __('All Event Categories', 'events'),
                'parent_items' => __('Parent Event Categories', 'events'),
                'parent_item_colon' => __('Parent Event Category:', 'events'),
                'edit_item' => __('Edit Event Category', 'events'),
                'update_item' => __('Update Event Category', 'events'),
                'add_new_item' => __('Add New Event Category', 'events'),
                'new_item_name' => __('New Event Category Name', 'events'),
                'separate_items_with_commas' => __('Separate event categories with commas', 'events'),
                'add_or_remove_items' => __('Add or remove events', 'events'),
                'choose_from_the_most_used' => __('Choose from most used event categories', 'events'),
            ]
        ]);
    }

    public function registerPostType(): void
    {
        $labels = [
            'name' => __('Events', 'events'),
            'singular_name' => __('Event', 'events'),
            'menu_name' => __('Events', 'events'),
            'add_new_item' => __('Add New Event', 'events'),
            'edit' => __('Edit', 'events'),
            'edit_item' => __('Edit Event', 'events'),
            'view' => __('View', 'events'),
            'view_item' => __('View Event', 'events'),
            'search_items' => __('Search Events', 'events'),
            'not_found' => __('No Events Found', 'events'),
            'not_found_in_trash' => __('No Events Found in Trash', 'events'),
            'parent' => __('Parent Event', 'events'),
        ];

        $event_post_type = [
            'public' => true,
            'hierarchical' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'rewrite' => ['slug' => 'events', 'with_front' => false],
            'has_archive' => true,
            'supports' => ['title','editor','excerpt','thumbnail','author','custom-fields'],
            'template' => [
                    ['ctx-blocks/grid-row', [], [
                        ['ctx-blocks/grid-column', ['widthLarge' => 2], [['core/paragraph', ['placeholder' => 'Event-Beschreibung']],]],
                        ['ctx-blocks/grid-column', ['widthLarge' => 1], [
                            ['events-manager/details', []],
                        ]]
                    ]],
                    ['core/separator'],
                    ['core/group',
                        ['layout' => ['type' => 'flex', 'flexWrap' => 'nowrap', 'justifyContent' => 'right']],
                        [['events-manager/booking', ['title' => 'Anmeldung']]]
                    ]
            ],
            'label' => __('Events', 'events'),
            'description' => __('Display events on your blog.', 'events'),
            'labels' => $labels,
            'menu_icon' => 'dashicons-calendar-alt'
        ];

        register_post_type(self::POST_TYPE, $event_post_type);
    }

    public function registerMeta(): void
    {
        $metadata = [
            [ "name" => "_booking_form","type" => "number"],
            [ "name" => "_attendee_form","type" => "number"],
            [ "name" => "_speaker_id","type" => "number"],
            [ "name" => "_location_id","type" => "number"],
            [ "name" => "_event_audience","type" => "string"],
            [ "name" => "_event_start","type" => "string"],
            [ "name" => "_event_end","type" => "string"],
            [ "name" => "_event_all_day","type" => "boolean"],
            [ "name" => "_event_rsvp_end","type" => "string"],
            [ "name" => "_event_rsvp_start","type" => "string"],
            [ "name" => "_event_rsvp","type" => "boolean"],
            [ "name" => "_event_spaces","type" => "number"],
            [ "name" => "_event_rsvp_spaces","type" => "number"],
            [ "name" => "_event_rsvp_donation","type" => "boolean"],
            [ "name" => "_event_recurrence_id", "type" => "number"],
            [ "name" => "_is_detached", "type" => "boolean"],

        ];

        foreach ($metadata as $meta) {
            register_post_meta(self::POST_TYPE, $meta['name'], [
                'type' => $meta['type'],
                'single'       => true,

                'sanitize_callback' => null,
                'auth_callback' => function () {
                    return current_user_can('edit_posts');
                },
                'show_in_rest' => [
                    'schema' => [
                        'type' => $meta['type']
                    ]
                ]
            ]);
        }

        register_post_meta('event', '_event_coupons', [
            'type' => 'array',
            'single' => true,
            'show_in_rest' => [
                'schema' => [
                    'type'  => 'array',
                    'items' => [
                        'type' => 'integer'
                    ],
                ]
            ],
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            }
        ]);

        register_post_meta('event', '_event_tickets', [
            'type' => 'array',
            'single' => true,
            'show_in_rest' => [
                'schema' => [
                    'type'  => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'ticket_id' => [
                                'type' => 'string'
                            ],
                            'ticket_name' => [
                                'type' => 'string'
                            ],
                            'ticket_description' => [
                                'type' => 'string'
                            ],
                            'ticket_price' => [
                                'type' => 'number'
                            ],
                            'ticket_max' => [
                                'type' => 'integer'
                            ],
                            'ticket_min' => [
                                'type' => 'integer'
                            ],
                            'ticket_spaces' => [
                                'type' => 'integer'
                            ],
                            'ticket_start' => [
                                'type' => 'string'
                            ],
                            'ticket_end' => [
                                'type' => 'string'
                            ],
                            'ticket_active' => [
                                'type' => 'boolean',
                                'default' => true
                            ],
                            'ticket_order' => [
                                'type' => 'number'
                            ],
                            'ticket_form' => [
                                'type' => 'integer'
                            ],
                            'ticket_enabled' => [
                                'type' => 'integer'
                            ],

                        ]
                    ],
                ]
            ],
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            }
        ]);

        register_meta('event', '_event_mails', [
            'type' => 'array',
            'single' => true,
            'show_in_rest' => [
                'schema' => [
                    'type'  => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'gateway'   => [ 'type' => 'string' ],
                            'status'    => [ 'type' => 'string' ],
                            'recipient' => [ 'type' => 'string' ],
                            'subject'   => [ 'type' => 'string' ],
                            'message'   => [ 'type' => 'string' ],
                            'enabled'   => [ 'type' => 'boolean' ],
                            'locale'    => [ 'type' => 'string' ]
                        ]
                    ],
                ],
            ],
            'sanitize_callback' => null,
            'auth_callback' => fn() => current_user_can('edit_posts'),
        ]);
    }
}
