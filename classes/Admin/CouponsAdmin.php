<?php

namespace Contexis\Events\Admin;

use Contexis\Events\Intl\Price;
use Contexis\Events\PostTypes\CouponPost;

class CouponsAdmin {
	
	public static function init() : self {
		$instance = new self();
		add_filter('manage_'.CouponPost::POST_TYPE.'_posts_columns' , array($instance,'columns_add'));
		add_action('manage_'.CouponPost::POST_TYPE.'_posts_custom_column' , array($instance,'columns_output'),10,2 );
		return $instance;
	}

	public function columns_add($columns) {
		$columns['coupon_code'] = __('Coupon Code','events');
		$columns['coupon_discount'] = __('Discount','events');
		$columns['coupon_expiry'] = __('Expiry Date','events');
		return $columns;
	}

	public function columns_output($column, $post_id) {
		switch ($column) {
			case 'coupon_code':
				echo get_post_meta($post_id, '_coupon_code', true);
				break;
			case 'coupon_discount':
				$value = get_post_meta($post_id, '_coupon_value', true);
				echo get_post_meta($post_id, '_coupon_type', true) == 'fixed' ? Price::format($value) : $value . '%';
				break;
			case 'coupon_expiry':
				echo get_post_meta($post_id, '_coupon_expiry', true);
				break;
		}
	}
}