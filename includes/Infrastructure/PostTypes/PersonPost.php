<?php

namespace Contexis\Events\Infrastructure\PostTypes;

use Contexis\Events\Core\Contracts\HasMetaData;
use Contexis\Events\Core\Contracts\PostType;

class PersonPost extends AbstractPostType implements HasMetaData
{
    public const POST_TYPE = 'ctx-event-person';

    private array $metadata = [
        [ "name" => "_person_email","type" => "string"],
        [ "name" => "_person_first_name","type" => "string"],
        [ "name" => "_person_last_name","type" => "string"],
        [ "name" => "_person_phone","type" => "string"],
        [ "name" => "_person_gender","type" => "string"],
        [ "name" => "_person_website","type" => "string"],
        [ "name" => "_person_prefix","type" => "string"],
        [ "name" => "_person_suffix","type" => "string"],
        [ "name" => "_person_position","type" => "string"],
        [ "name" => "_person_organization","type" => "string"]
    ];

    public function registerPostType(): void
    {
        $args = apply_filters(self::POST_TYPE, [
            'public' => false,
            'hierarchical' => false,
            'show_in_rest' => true,
            'show_in_admin_bar' => true,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=event',
            'show_in_nav_menus' => true,
            'can_export' => true,
            'publicly_queryable' => false,
            'rewrite' => ['slug' => 'event-contact', 'with_front' => false],
            'query_var' => false,
            'has_archive' => false,
            'supports' => ['title', 'thumbnail', 'editor', 'excerpt', 'custom-fields'],
            'label' => __('Persons', 'events'),
            'description' => __('Person for an event.', 'events'),
            'labels' => [
                'name' => __('Persons', 'events'),
                'singular_name' => __('Person', 'events'),
                'menu_name' => __('Persons', 'events'),
                'add_new' => __('Add Person', 'events'),
                'add_new_item' => __('Add New Person', 'events'),
                'edit' => __('Edit', 'events'),
                'edit_item' => __('Edit Person', 'events'),
                'new_item' => __('New Person', 'events'),
                'view' => __('View', 'events'),
                'view_item' => __('View Person', 'events'),
                'search_items' => __('Search Person', 'events'),
                'not_found' => __('No Person Found', 'events'),
                'not_found_in_trash' => __('No Person Found in Trash', 'events'),
                'parent' => __('Parent Person', 'events'),
            ],
        ]);

        register_post_type(self::POST_TYPE, $args);
    }

    public function registerMeta(): void
    {

        foreach ($this->metadata as $meta) {
            register_post_meta(self::POST_TYPE, $meta['name'], [
                'type' => $meta['type'],
                'single' => true,
                'show_in_rest' => true,
                'auth_callback' => function () {
                    return current_user_can('edit_posts');
                },
            ]);
        }
    }
}
