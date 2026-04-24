<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Application\Contracts\EventOptions;
use Contexis\Events\Event\Infrastructure\EventMeta;
use Contexis\Events\Platform\Wordpress\PluginInfo;
use Contexis\Events\Shared\Infrastructure\Abstracts\PostType;
use Contexis\Events\Shared\Infrastructure\Contracts\HasHooks;
use Contexis\Events\Shared\Infrastructure\Contracts\HasMetaData;
use Contexis\Events\Shared\Infrastructure\Contracts\HasPatterns;
use Contexis\Events\Shared\Infrastructure\Contracts\HasTaxonomies;

class EventPost extends PostType implements HasTaxonomies, HasMetaData, HasPatterns, HasHooks
{
    public const POST_TYPE = "ctx-event";
    public const CATEGORIES = 'ctx-event-categories';
    
    
    

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
        EventTaxonomy::register($this->getSlug());
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

    public function registerPatterns(): void
    {
        EventPatterns::register();
    }

	public function registerHooks(): void
	{
		$this->hooks->register();
		
	}
   
}
