<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Infrastructure;

use Contexis\Events\Platform\Wordpress\Admin\AdminMenu;
use Contexis\Events\Shared\Infrastructure\Abstracts\PostType;

class AttendeeFormPost extends PostType
{
    public const POST_TYPE = 'ctx-attendee-form';
	public const TAGS = 'ctx_form_tag';
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
            'label' => __('Attendee Forms', 'ctx-events'),
            'description' => __('Form for the attendee data', 'ctx-events'),

            'template' => [
                ['ctx-events/form-container', ['lock' => [
                    'move'   => true,
                    'remove' => true,
                ]], [
                    ['ctx-events/form-text', [
                        'lock'     => ['remove' => true, 'move' => false],
                        'required' => false,
                        'width'    => 3,
                        'label'    => __('First Name', 'ctx-events'),
                        'name'     => 'first_name',
                    ]],
                    ['ctx-events/form-text', [
                        'lock'     => ['remove' => true, 'move' => false],
                        'required' => false,
                        'width'    => 3,
                        'label'    => __('Last Name', 'ctx-events'),
                        'name'     => 'last_name',
                    ]],
                ]]
            ],
            'labels' => [
                'name' => __('Attendee Form', 'ctx-events'),
                'singular_name' => __('Attendee Form', 'ctx-events'),
                'menu_name' => __('Attendee Forms', 'ctx-events'),
                'add_new' => __('Add Attendee Form', 'ctx-events'),
                'add_new_item' => __('Add New Attendee Form', 'ctx-events'),
                'edit' => __('Edit', 'ctx-events'),
                'edit_item' => __('Edit Attendee Form', 'ctx-events'),
                'new_item' => __('New Attendee Form', 'ctx-events'),
                'view' => __('View', 'ctx-events'),
                'view_item' => __('View Attendee Form', 'ctx-events'),
                'search_items' => __('Search Forms', 'ctx-events'),
                'not_found' => __('No Forms Found', 'ctx-events'),
                'not_found_in_trash' => __('No Forms Found in Trash', 'ctx-events'),
                'parent' => __('Parent Form', 'ctx-events'),
            ],
        ];

        register_post_type(AttendeeFormPost::POST_TYPE, $args);
    }
}
