<?php
declare(strict_types=1);

namespace Contexis\Events\Person\Infrastructure;

use Contexis\Events\Event\Infrastructure\EventPost;
use Contexis\Events\Shared\Infrastructure\Abstracts\PostType;
use Contexis\Events\Shared\Infrastructure\Contracts\HasMetaData;
use Contexis\Events\Shared\Infrastructure\Contracts\HasTaxonomies;

class PersonPost extends PostType implements HasMetaData, HasTaxonomies
{
    public const POST_TYPE = 'ctx-event-person';
    public const CATEGORY = 'ctx-event-person-category';

    public function registerPostType(): void
    {
        $args = apply_filters(self::POST_TYPE, [
            'public' => false,
            'hierarchical' => false,
            'show_in_rest' => true,
            'show_in_admin_bar' => true,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=ctx-event',
            'show_in_nav_menus' => true,
            'can_export' => true,
            'publicly_queryable' => false,
            'rewrite' => ['slug' => 'event-contact', 'with_front' => false],
            'query_var' => false,
            'has_archive' => false,
            'template_lock' => 'all',
            'template' => [
                ['ctx-events/person-editor', []],
            ],
            'supports' => ['title', 'thumbnail', 'editor', 'excerpt', 'custom-fields'],
            'label' => __('Persons', 'ctx-events'),
            'description' => __('Person for an event.', 'ctx-events'),
            'labels' => [
                'name' => __('Persons', 'ctx-events'),
                'singular_name' => __('Person', 'ctx-events'),
                'menu_name' => __('Persons', 'ctx-events'),
                'add_new' => __('Add Person', 'ctx-events'),
                'add_new_item' => __('Add New Person', 'ctx-events'),
                'edit' => __('Edit', 'ctx-events'),
                'edit_item' => __('Edit Person', 'ctx-events'),
                'new_item' => __('New Person', 'ctx-events'),
                'view' => __('View', 'ctx-events'),
                'view_item' => __('View Person', 'ctx-events'),
                'search_items' => __('Search Person', 'ctx-events'),
                'not_found' => __('No Person Found', 'ctx-events'),
                'not_found_in_trash' => __('No Person Found in Trash', 'ctx-events'),
                'parent' => __('Parent Person', 'ctx-events'),
            ],
        ]);

        register_post_type(self::POST_TYPE, $args);
    }

    public function registerMeta(): void
    {
        PersonMeta::registerAll(self::POST_TYPE);
    }

    public function registerTaxonomies(): void
    {

        register_taxonomy(self::CATEGORY, [self::POST_TYPE], [
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => EventPost::POST_TYPE . '/person-categories', 'hierarchical' => true,'with_front' => false],
            'show_in_nav_menus' => true,
            'label' => __('Person Categories', 'ctx-events'),
            'singular_label' => __('Person Category', 'ctx-events'),
            'labels' => [
                'name' => __('Person Categories', 'ctx-events'),
                'singular_name' => __('Person Category', 'ctx-events'),
                'search_items' => __('Search Person Categories', 'ctx-events'),
                'popular_items' => __('Popular Person Categories', 'ctx-events'),
                'all_items' => __('All Person Categories', 'ctx-events'),
                'parent_items' => __('Parent Person Categories', 'ctx-events'),
                'parent_item_colon' => __('Parent Person Category:', 'ctx-events'),
                'edit_item' => __('Edit Person Category', 'ctx-events'),
                'update_item' => __('Update Person Category', 'ctx-events'),
                'add_new_item' => __('Add New Person Category', 'ctx-events'),
                'new_item_name' => __('New Person Category Name', 'ctx-events'),
                'separate_items_with_commas' => __('Separate person categories with commas', 'ctx-events'),
                'add_or_remove_items' => __('Add or remove persons', 'ctx-events'),
                'choose_from_the_most_used' => __('Choose from most used person categories', 'ctx-events'),
            ]
        ]);
    }
}
