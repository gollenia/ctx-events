<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Infrastructure;

use Contexis\Events\Platform\Wordpress\Admin\AdminMenu;
use Contexis\Events\Shared\Infrastructure\Abstracts\PostType;

class BookingFormPost extends PostType
{
    public const POST_TYPE = 'ctx-booking-form';

    public function registerPostType(): void
    {
        $args = [
            'public' => false,
            'hierarchical' => false,
            'show_in_rest' => true,
            'show_in_admin_bar' => true,
            'show_ui' => true,
            'show_in_menu' => AdminMenu::MENU_SLUG,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'query_var' => true,
            'has_archive' => false,
            'supports' => ['title', 'excerpt', 'editor'],
            'label' => __('Booking Forms', 'events'),
            'description' => __('Forms for the booking data', 'events'),
            'template' => [
                ['ctx-events/form-container', [
                    'templateLock' => false,
                    'lock' => [
                        'move'   => false,
                        'remove' => true
                    ]
                ], [
                    [
                        'ctx-events/form-email',
                        [
                            "lock" => ["remove" => true, "move" => false],
                            "required" => true,
                            "label" => __('Email', 'events'),
                            "name" => 'user_email'
                        ]
                    ],
                    ['ctx-events/form-text', [
                        "lock" => ["remove" => true, "move" => false],
                        "required" => true,
                        "width" => 3,
                        "label" => __('First Name', 'events'),
                        "name" => 'first_name'
                    ]],
                    ['ctx-events/form-text', [
                        "lock" => ["remove" => true, "move" => false],
                        "required" => true,
                        "width" => 3,
                        "label" => __('Last Name', 'events'),
                        "name" => 'last_name'
                    ]]
                ]]
            ],
            'template_lock' => 'all',
            'labels' => [
                'name' => __('Booking Forms', 'events'),
                'singular_name' => __('Booking Form', 'events'),
                'menu_name' => __('Booking Forms', 'events'),
                'add_new' => __('Add Booking Form', 'events'),
                'add_new_item' => __('Add New Booking Form', 'events'),
                'edit' => __('Edit', 'events'),
                'edit_item' => __('Edit Booking Form', 'events'),
                'new_item' => __('New Booking Form', 'events'),
                'view' => __('View', 'events'),
                'view_item' => __('View Booking Form', 'events'),
                'search_items' => __('Search Booking Forms', 'events'),
                'not_found' => __('No Booking Forms Found', 'events'),
                'not_found_in_trash' => __('No Booking Forms Found in Trash', 'events'),
                'parent' => __('Parent Booking Form', 'events'),
            ],
        ];

        register_post_type(BookingFormPost::POST_TYPE, $args);
    }
}
