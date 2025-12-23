<?php
declare(strict_types=1);

namespace Contexis\Events\Form\Infrastructure;

use Contexis\Events\Platform\Wordpress\Admin\AdminMenu;
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
                'show_in_menu' => AdminMenu::MENU_SLUG,
                'show_in_nav_menus' => true,
                'can_export' => true,
                'exclude_from_search' => true,
                'publicly_queryable' => false,
                'query_var' => true,
                'has_archive' => false,
                'supports' => ['title','excerpt','editor'],
                'label' => __('Forms', 'events'),
                'description' => __('Form for the registration data', 'events'),
                'template' => [
                    ['ctx-events/form-container', [], [
                        ['ctx-events/form-email', [
                            "lock" => ["remove" => true, "move" => false],
                            "required" => true, "label" => __('Email', 'events'),
                            "name" => 'user_email']
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
                        ]]]]
                ],
				'template_lock' => 'all',
                'labels' => [
                    'name' => __('Registration Form', 'events'),
                    'singular_name' => __('Form', 'events'),
                    'menu_name' => __('Forms', 'events'),
                    'add_new' => __('Add Registration Form', 'events'),
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
