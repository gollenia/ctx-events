<?php
declare(strict_types=1);

namespace Contexis\Events\Booking\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Abstracts\PostType;

class RegistrationFormPost extends PostType
{
    public const POST_TYPE = 'ctx-registration-form';

    public function registerPostType(): void
    {
        $args = [
                'public' => false,
                'hierarchical' => false,
                'show_in_rest' => true,
                'show_in_admin_bar' => true,
                'show_ui' => true,
                'show_in_menu' => false,
                'show_in_nav_menus' => false,
                'can_export' => true,
                'exclude_from_search' => true,
                'publicly_queryable' => false,
                'query_var' => true,
                'has_archive' => false,
                'supports' => ['title','excerpt','editor'],
                'label' => __('Forms', 'events'),
                'description' => __('Display forms on your blog.', 'events'),
                'template' => [
                    ['events-manager/form-container', [], [
                        ['events-manager/form-email', [
                            "lock" => ["remove" => true, "move" => false],
                            "required" => true, "label" => __('Email', 'events'),
                            "fieldid" => 'user_email']
                        ],
                        ['events-manager/form-text', [
                            "lock" => ["remove" => true, "move" => false],
                            "required" => true,
                            "width" => 3,
                            "label" => __('First Name', 'events'),
                            "fieldid" => 'first_name'
                        ]],
                        ['events-manager/form-text', [
                            "lock" => ["remove" => true, "move" => false],
                            "required" => true,
                            "width" => 3,
                            "label" => __('Last Name', 'events'),
                            "fieldid" => 'last_name'
                        ]]]]
                ],
                'labels' => [
                    'name' => __('Booking Form', 'events'),
                    'singular_name' => __('Form', 'events'),
                    'menu_name' => __('Forms', 'events'),
                    'add_new' => __('Add Booking Form', 'events'),
                    'add_new_item' => __('Add New Form', 'events'),
                    'edit' => __('Edit', 'events'),
                    'edit_item' => __('Edit Form', 'events'),
                    'new_item' => __('New Form', 'events'),
                    'view' => __('View', 'events'),
                    'view_item' => __('View Form', 'events'),
                    'search_items' => __('Search Forms', 'events'),
                    'not_found' => __('No Forms Found', 'events'),
                    'not_found_in_trash' => __('No Forms Found in Trash', 'events'),
                    'parent' => __('Parent Form', 'events'),
                ],
            ];

        register_post_type(self::POST_TYPE, $args);
    }
}
