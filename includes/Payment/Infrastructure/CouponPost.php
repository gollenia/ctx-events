<?php
declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Event\Application\Contracts\EventOptions;
use Contexis\Events\Event\Infrastructure\EventPost;
use Contexis\Events\Platform\Wordpress\Admin\AdminMenu;
use Contexis\Events\Shared\Infrastructure\Abstracts\PostType;
use Contexis\Events\Shared\Infrastructure\Contracts\HasHooks;
use Contexis\Events\Shared\Infrastructure\Contracts\HasMetaData;

class CouponPost extends PostType implements HasMetaData, HasHooks
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

	public function registerHooks(): void
    {
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', [$this, 'filterColumns']);
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', [$this, 'renderColumn'], 10, 2);
    }

	/**
     * @param array<string, string> $columns
     * @return array<string, string>
     */
    public function filterColumns(array $columns): array
    {
        return [
            'cb' => $columns['cb'] ?? '<input type="checkbox" />',
            'title' => $columns['title'] ?? __('Title', 'ctx-events'),
            'code' => __('Coupon Code', 'ctx-events'),
			'discount' => __('Discount', 'ctx-events'),
            'expires_at' => __('Expires At', 'ctx-events'),
			'usage' => __('Usage', 'ctx-events'),
			
        ];
    }

    public function renderColumn(string $column, int $postId): void
    {
        switch ($column) {

            case 'code':
                echo esc_html((string) get_post_meta($postId, CouponMeta::CODE, true) ?: '—');
                break;

            case 'discount':
                echo esc_html((string) get_post_meta($postId, CouponMeta::VALUE, true) ?: '—') . ' ' . esc_html((string) get_post_meta($postId, CouponMeta::TYPE, true) === 'fixed' ? 'EUR' : '%');
				break;
			
			case 'expires_at':
				$expiresAt = get_post_meta($postId, CouponMeta::EXPIRES_AT, true);
				echo esc_html($expiresAt ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($expiresAt)) : '—');
                break;

			case 'usage':
				$usageCount = get_post_meta($postId, CouponMeta::USED, true);
				$usageLimit = get_post_meta($postId, CouponMeta::LIMIT, true);
				echo esc_html(($usageCount !== '' ? $usageCount : '0') . ' / ' . ($usageLimit !== '' ? $usageLimit : '∞'));
				break;
        }

    }
}
