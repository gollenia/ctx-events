<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Abstracts\PostType;
use Contexis\Events\Shared\Infrastructure\Contracts\HasMetaData;

class RecurringEventPost extends PostType implements HasMetaData
{
    public const POST_TYPE = 'ctx-event-recurring';

    public function registerPostType(): void
    {
        $labels = [
            'name' => __('Recurring Events', 'ctx-events'),
            'singular_name' => __('Recurring Event', 'ctx-events'),
            'menu_name' => __('Recurring Events', 'ctx-events'),
            'add_new' => __('Add Recurring Event', 'ctx-events'),
            'add_new_item' => __('Add New Recurring Event', 'ctx-events'),
            'edit' => __('Edit', 'ctx-events'),
            'edit_item' => __('Edit Recurring Event', 'ctx-events'),
            'new_item' => __('New Recurring Event', 'ctx-events'),
            'view' => __('View', 'ctx-events'),
            'view_item' => __('View Recurring Event', 'ctx-events'),
            'search_items' => __('Search Recurring Events', 'ctx-events'),
            'not_found' => __('No Recurring Events Found', 'ctx-events'),
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
            'label' => __('Recurring Events', 'ctx-events'),
            'description' => __('Recurring Events Template', 'ctx-events'),
            'labels' => $labels
        ];

        register_post_type(self::POST_TYPE, $post_type);

        // The terms are owned by EventTaxonomy and registered for ctx-event.
        // Attach that existing taxonomy to the series type; do not register it twice.
        register_taxonomy_for_object_type(EventTaxonomy::TAGS, self::POST_TYPE);
        register_taxonomy_for_object_type(EventTaxonomy::CATEGORIES, self::POST_TYPE);
    }

    public function registerMeta(): void
    {
        RecurringEventMeta::registerAll(self::POST_TYPE);
    }
}
