<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Infrastructure\EventMeta as InfrastructureEventMeta;
use Contexis\Events\Event\Infrastructure\EventMeta;
use Contexis\Events\Platform\Wordpress\Admin\AdminMenu;
use Contexis\Events\Shared\Infrastructure\Abstracts\PostType;
use Contexis\Events\Shared\Infrastructure\Contracts\HasHooks;
use Contexis\Events\Shared\Infrastructure\Contracts\HasMetaData;
use Contexis\Events\Shared\Infrastructure\Contracts\HasTaxonomies;

class EventPost extends PostType implements HasTaxonomies, HasMetaData, HasHooks
{
    public const POST_TYPE = "ctx-event";
    public const CATEGORIES = 'ctx-event-categories';
    public const TAGS = 'ctx-event-tags';

	public function __construct(
		private readonly EventHooks $hooks
	)
	{
		
	}

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
            'show_in_menu' => false,
            'show_in_rest' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'rewrite' => ['slug' => 'events', 'with_front' => false],
            'has_archive' => true,
            'supports' => ['title','editor','excerpt','thumbnail','author','custom-fields'],
            'label' => __('Events', 'events'),
            'description' => __('Display events on your blog.', 'events'),
            'labels' => $labels,
            'menu_icon' => 'dashicons-calendar-alt'
        ];

        register_post_type(self::POST_TYPE, $event_post_type);
    }

    public function registerMeta(): void
    {
        EventMeta::registerAll(self::POST_TYPE);
    }

	public function registerHooks(): void
	{
		$this->hooks->register();	
	}
}
