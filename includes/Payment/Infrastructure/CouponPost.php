<?php

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Event\Infrastructure\EventPost;
use Contexis\Events\Shared\Infrastructure\Abstracts\PostType;
use Contexis\Events\Shared\Infrastructure\Contracts\HasMetaData;

class CouponPost extends PostType implements HasMetaData
{
    public const POST_TYPE = "ctx-coupon";



    public static function getAdminUrl(): string
    {
        return admin_url('edit.php?post_type=' . self::POST_TYPE);
    }

    public function registerPostType(): void
    {
        $labels = [
            'name' => __('Coupons', 'events'),
            'singular_name' => __('Coupon', 'events'),
            'menu_name' => __('Coupons', 'events'),
            'add_new_item' => __('Add New Coupon', 'events'),
            'edit' => __('Edit', 'events'),
            'edit_item' => __('Edit Coupon', 'events'),
            'view' => __('View', 'events'),
            'view_item' => __('View Coupon', 'events'),
            'search_items' => __('Search Coupon', 'events'),
            'not_found' => __('No Coupons Found', 'events'),
            'not_found_in_trash' => __('No Coupons Found in Trash', 'events'),
            'parent' => __('Parent Coupon', 'events'),
        ];

        $coupon_post_type = [
            'public' => true,
            'hierarchical' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'exclude_from_search' => !get_option('dbem_cp_events_search_results'),
            'show_in_menu' => 'edit.php?post_type=' . EventPost::POST_TYPE,
            'publicly_queryable' => false,
            'has_archive' => true,
            'supports' => ['title','editor','excerpt','thumbnail','author','custom-fields'],
            'template' => [
                [ 'events/coupon-form' ],
            ],
            'template_lock' => 'all',
            'label' => __('Coupons', 'events'),
            'description' => __('Manage coupons for event booking', 'events'),
            'labels' => $labels,
        ];

        register_post_type(self::POST_TYPE, $coupon_post_type);
    }

    public function registerMeta(): void
    {
        CouponMeta::registerAll(self::POST_TYPE);
    }
}
