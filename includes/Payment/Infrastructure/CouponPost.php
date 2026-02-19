<?php
declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Event\Infrastructure\EventPost;
use Contexis\Events\Platform\Wordpress\Admin\AdminMenu;
use Contexis\Events\Shared\Infrastructure\Abstracts\PostType;
use Contexis\Events\Shared\Infrastructure\Contracts\HasMetaData;

class CouponPost extends PostType implements HasMetaData
{
    public const POST_TYPE = "ctx-event-coupon";

    public static function getAdminUrl(): string
    {
        return admin_url('edit.php?post_type=' . self::POST_TYPE);
    }

    public function registerPostType(): void
    {
        $labels = [
            'name' => __('Coupons', 'ctx-events'),
            'singular_name' => __('Coupon', 'ctx-events'),
            'menu_name' => __('Coupons', 'ctx-events'),
            'add_new_item' => __('Add New Coupon', 'ctx-events'),
            'edit' => __('Edit', 'ctx-events'),
            'edit_item' => __('Edit Coupon', 'ctx-events'),
            'view' => __('View', 'ctx-events'),
            'view_item' => __('View Coupon', 'ctx-events'),
            'search_items' => __('Search Coupon', 'ctx-events'),
            'not_found' => __('No Coupons Found', 'ctx-events'),
            'not_found_in_trash' => __('No Coupons Found in Trash', 'ctx-events'),
            'parent' => __('Parent Coupon', 'ctx-events'),
        ];

        $coupon_post_type = [
            'public' => true,
            'hierarchical' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'exclude_from_search' => true,
            'show_in_menu' => AdminMenu::MENU_SLUG,
            'publicly_queryable' => false,
            'has_archive' => true,
            'supports' => ['title','editor','excerpt','thumbnail','author','custom-fields'],
            'template' => [
                [ 'ctx-events/coupon-editor' ],
            ],
            'template_lock' => 'all',
            'label' => __('Coupons', 'ctx-events'),
            'description' => __('Manage coupons for event booking', 'ctx-events'),
            'labels' => $labels,
        ];

        register_post_type(self::POST_TYPE, $coupon_post_type);
    }

    public function registerMeta(): void
    {
        CouponMeta::registerAll(self::POST_TYPE);
    }
}
