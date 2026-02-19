<?php
declare(strict_types=1);

namespace Contexis\Events\Location\Infrastructure;

use Contexis\Events\Event\Infrastructure\EventPost;
use Contexis\Events\Platform\Wordpress\Admin\AdminMenu;
use Contexis\Events\Shared\Infrastructure\Abstracts\PostType;
use Contexis\Events\Shared\Infrastructure\Contracts\HasMetaData;

class LocationPost extends PostType implements HasMetaData
{
    public const POST_TYPE = 'ctx-event-location';

    public static function getSlug(): string
    {
        return self::POST_TYPE;
    }

    public function registerPostType(): void
    {
        $labels = [
            'name' => __('Locations', 'ctx-events'),
            'singular_name' => __('Location', 'ctx-events'),
            'menu_name' => __('Locations', 'ctx-events'),
            'add_new' => __('Add Location', 'ctx-events'),
            'add_new_item' => __('Add New Location', 'ctx-events'),
            'edit' => __('Edit', 'ctx-events'),
            'edit_item' => __('Edit Location', 'ctx-events'),
            'new_item' => __('New Location', 'ctx-events'),
            'view' => __('View', 'ctx-events'),
            'view_item' => __('View Location', 'ctx-events'),
            'search_items' => __('Search Locations', 'ctx-events'),
            'not_found' => __('No Locations Found', 'ctx-events'),
            'not_found_in_trash' => __('No Locations Found in Trash', 'ctx-events'),
            'parent' => __('Parent Location', 'ctx-events'),
        ];

        $post_type = [
            'public' => true,
            'hierarchical' => false,
            'show_in_rest' => true,
            'show_in_admin_bar' => true,
            'show_ui' => true,
            'show_in_menu' => AdminMenu::MENU_SLUG,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'rewrite' => ['slug' => self::getSlug(), 'with_front' => false],
            'template' => [[ 'ctx-events/location-editor' ]],
            'query_var' => true,
            'has_archive' => false,
            'template_lock' => 'all',
            'supports' => apply_filters('em_cp_location_supports', ['title','excerpt','thumbnail','editor','custom-fields']),
            'label' => __('Locations', 'ctx-events'),
            'description' => __('Display locations on your blog.', 'ctx-events'),
            'labels' => $labels
        ];

        register_post_type(self::POST_TYPE, $post_type);
    }


    public function registerMeta(): void
    {
        LocationMeta::registerAll(self::POST_TYPE);
    }
}
