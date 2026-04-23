<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Application\Contracts\EventOptions;
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
		private readonly EventHooks $hooks,
		private readonly EventOptions $options
	)
	{
		
	}

    public function getSlug($suffix = ''): string
    {
        return $this->options->getEventsSlug() . $suffix;
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
                'name' => __('Event Tags', 'ctx-events'),
                'singular_name' => __('Event Tag', 'ctx-events'),
                'search_items' => __('Search Event Tags', 'ctx-events'),
                'popular_items' => __('Popular Event Tags', 'ctx-events'),
                'all_items' => __('All Event Tags', 'ctx-events'),
                'parent_items' => __('Parent Event Tags', 'ctx-events'),
                'parent_item_colon' => __('Parent Event Tag:', 'ctx-events'),
                'edit_item' => __('Edit Event Tag', 'ctx-events'),
                'update_item' => __('Update Event Tag', 'ctx-events'),
                'add_new_item' => __('Add New Event Tag', 'ctx-events'),
                'new_item_name' => __('New Event Tag Name', 'ctx-events'),
                'separate_items_with_commas' => __('Separate event tags with commas', 'ctx-events'),
                'add_or_remove_items' => __('Add or remove events', 'ctx-events'),
                'choose_from_the_most_used' => __('Choose from most used event tags', 'ctx-events'),
            ]
        ]);

        register_taxonomy(self::POST_TYPE . '-categories', [self::POST_TYPE], [
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => $this->getSlug('/categories'), 'hierarchical' => true,'with_front' => false],
            'show_in_nav_menus' => true,
            'label' => __('Event Categories', 'ctx-events'),
            'singular_label' => __('Event Category', 'ctx-events'),
            'labels' => [
                'name' => __('Event Categories', 'ctx-events'),
                'singular_name' => __('Event Category', 'ctx-events'),
                'search_items' => __('Search Event Categories', 'ctx-events'),
                'popular_items' => __('Popular Event Categories', 'ctx-events'),
                'all_items' => __('All Event Categories', 'ctx-events'),
                'parent_items' => __('Parent Event Categories', 'ctx-events'),
                'parent_item_colon' => __('Parent Event Category:', 'ctx-events'),
                'edit_item' => __('Edit Event Category', 'ctx-events'),
                'update_item' => __('Update Event Category', 'ctx-events'),
                'add_new_item' => __('Add New Event Category', 'ctx-events'),
                'new_item_name' => __('New Event Category Name', 'ctx-events'),
                'separate_items_with_commas' => __('Separate event categories with commas', 'ctx-events'),
                'add_or_remove_items' => __('Add or remove events', 'ctx-events'),
                'choose_from_the_most_used' => __('Choose from most used event categories', 'ctx-events'),
            ]
        ]);
    }

    public function registerPostType(): void
    {
        $labels = [
            'name' => __('Events', 'ctx-events'),
            'singular_name' => __('Event', 'ctx-events'),
            'menu_name' => __('Events', 'ctx-events'),
            'add_new_item' => __('Add New Event', 'ctx-events'),
            'edit' => __('Edit', 'ctx-events'),
            'edit_item' => __('Edit Event', 'ctx-events'),
            'view' => __('View', 'ctx-events'),
            'view_item' => __('View Event', 'ctx-events'),
            'search_items' => __('Search Events', 'ctx-events'),
            'not_found' => __('No Events Found', 'ctx-events'),
            'not_found_in_trash' => __('No Events Found in Trash', 'ctx-events'),
            'parent' => __('Parent Event', 'ctx-events'),
        ];

        $event_post_type = [
            'public' => true,
            'hierarchical' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_rest' => true,
            'show_in_nav_menus' => true,
			'show_in_admin_bar' => true,
            'can_export' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'rewrite' => ['slug' => $this->getSlug(), 'with_front' => false],
            'has_archive' => true,
            'supports' => ['title','editor','excerpt','thumbnail','author','custom-fields'],
            'label' => __('Events', 'ctx-events'),
            'description' => __('Display events on your blog.', 'ctx-events'),
            'labels' => $labels,
            'menu_icon' => 'dashicons-calendar-alt'
        ];
        register_post_type(self::POST_TYPE, $event_post_type);
		
		register_post_status('cancelled', [
			'label'                     => _x('Cancelled', 'post status', 'ctx-events'),
			'public'                    => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop('Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'ctx-events'),
		]);
		
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
