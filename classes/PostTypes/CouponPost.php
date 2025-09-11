<?php

namespace Contexis\Events\PostTypes;

use Contexis\Events\Core\Contracts\PostType;


class CouponPost implements PostType {

	const POST_TYPE = "coupon";

	private array $metadata = [
		[ "name" => "_coupon_code","type" => "string"],
		[ "name" => "_coupon_type","type" => "string"],
		[ "name" => "_coupon_value","type" => "number"],
		[ "name" => "_coupon_expiry","type" => "string"],
		[ "name" => "_coupon_limit","type" => "number"],
		[ "name" => "_coupon_used","type" => "number"],
		[ "name" => "_coupon_status","type" => "string"],
		[ "name" => "_coupon_global","type" => "boolean"]
	];
	
	public static function init() : self {
		$instance = new self;
		add_action('init', array($instance, 'register_post_type'));
		add_action('init', array($instance, 'register_meta'));	
		add_action('rest_api_init', [$instance, 'add_rest_fields']);
		return $instance;
	}	

	public static function get_slug(): string {
        return self::POST_TYPE;
    }

	public static function get_admin_url(): string {
        return admin_url('edit.php?post_type=' . self::POST_TYPE);
    }
	
	public function register_post_type() : void {
		$labels = [
			'name' => __('Coupons','events'),
			'singular_name' => __('Coupon','events'),
			'menu_name' => __('Coupons','events'),
			'add_new_item' => __('Add New Coupon','events'),
			'edit' => __('Edit','events'),
			'edit_item' => __('Edit Coupon','events'),
			'view' => __('View','events'),
			'view_item' => __('View Coupon','events'),
			'search_items' => __('Search Coupon','events'),
			'not_found' => __('No Coupons Found','events'),
			'not_found_in_trash' => __('No Coupons Found in Trash','events'),
			'parent' => __('Parent Coupon','events'),
		];
		
		$coupon_post_type = [	
			'public' => true,
			'hierarchical' => false,
			'show_ui' => true,
			'show_in_rest' => true,
			'show_in_nav_menus'=>true,
			'can_export' => true,
			'exclude_from_search' => !get_option('dbem_cp_events_search_results'),
			'show_in_menu' => 'edit.php?post_type=' . EventPost::POST_TYPE,
			'publicly_queryable' => false,
			'rewrite' => ['slug' => self::get_slug(),'with_front'=>false],
			'has_archive' => true,
			'supports' => ['title','editor','excerpt','thumbnail','author','custom-fields'],
			'template' => [
				[ 'events/coupon-form' ],
			],
			'template_lock' => 'all',
			'label' => __('Coupons','events'),
			'description' => __('Manage coupons for event booking','events'),
			'labels' => $labels,
		];
		
		register_post_type( self::POST_TYPE, $coupon_post_type );
	}

	public function register_meta() : void {

		foreach($this->metadata as $meta){
			register_post_meta( self::POST_TYPE, $meta['name'], [
				'type' => $meta['type'],
				'single'       => true,
				'sanitize_callback' => null,
				'auth_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
				'show_in_rest' => [
					'schema' => [	
						'type' => $meta['type']
					]
				]
			]);
		}
	}

	public function add_rest_fields() : void {
		foreach($this->metadata as $meta){
			register_rest_field( self::POST_TYPE, $meta['name'], [
				'get_callback' => function($object) use ($meta) {
					return get_post_meta($object['id'], $meta['name'], true);
				},
				'schema' => [
					'type' => $meta['type'],
					'context' => ['view', 'edit'],
				],
			]);
		}
	}
}
