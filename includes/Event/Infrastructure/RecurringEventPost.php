<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Abstracts\PostType;
use Contexis\Events\Event\Infrastructure\EventPost;

class RecurringEventPost implements PostType
{
    public const POST_TYPE = 'event-recurring';

    public static function init(): self
    {
        $instance = new self();
        add_action('init', array($instance, 'register_post_type'));
        add_action('init', array($instance, 'register_meta'));
        return $instance;
    }

    public function registerPostType(): void
    {
        $labels = [
            'name' => __('Recurring Events', 'events'),
            'singular_name' => __('Recurring Event', 'events'),
            'menu_name' => __('Recurring Events', 'events'),
            'add_new' => __('Add Recurring Event', 'events'),
            'add_new_item' => __('Add New Recurring Event', 'events'),
            'edit' => __('Edit', 'events'),
            'edit_item' => __('Edit Recurring Event', 'events'),
            'new_item' => __('New Recurring Event', 'events'),
            'view' => __('View', 'events'),
            'view_item' => __('View Recurring Event', 'events'),
            'search_items' => __('Search Recurring Events', 'events'),
            'not_found' => __('No Recurring Events Found', 'events'),
        ];

        $post_type = [
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_in_admin_bar' => true,
            'show_in_menu' => 'edit.php?post_type=' . EventPost::POST_TYPE,
            'show_in_nav_menus' => false,
            'publicly_queryable' => true,
            'exclude_from_search' => true,
            'has_archive' => false,
            'can_export' => true,
            'hierarchical' => false,
            'supports' => ['title','editor','excerpt','thumbnail','author','custom-fields'],
            'rewrite' => ['slug' => 'events-recurring','with_front' => false],
            'label' => __('Recurring Events', 'events'),
            'description' => __('Recurring Events Template', 'events'),
            'labels' => $labels
        ];

        register_post_type(self::POST_TYPE, $post_type);
    }

    public function registerMeta(): void
    {

        $metadata = [
            [ "name" => "_event_start_date","type" => "string"],
            [ "name" => "_event_end_date","type" => "string"],
            [ "name" => "_event_start_time","type" => "string"],
            [ "name" => "_event_end_time","type" => "string"],
            [ "name" => "_event_all_day","type" => "boolean"],
            [ "name" => "_speaker_id","type" => "number"],
            [ "name" => "_location_id","type" => "number"],
            [ "name" => "_event_audience","type" => "string"],
            [ "name" => "_recurrence_interval","type" => "number"],
            [ "name" => "_recurrence_byweekno","type" => "number"],
            [ "name" => "_recurrence_byday","type" => "string"],
            [ "name" => "_recurrence_days","type" => "number"],
            [ "name" => "_recurrence_freq","type" => "string"]
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
    }
}
