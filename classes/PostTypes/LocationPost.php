<?php

namespace Contexis\Events\PostTypes;

use Contexis\Events\Interfaces\PostType;
use Contexis\Events\PostTypes\EventPost;

class LocationPost implements PostType {

	const POST_TYPE = 'location';

	public static function init() : self { 
		$instance = new self;
		add_action( 'init', array($instance, 'register_post_type') );
		add_action( 'init', array($instance, 'meta_query_filter') );
		add_action( 'init', array($instance, 'register_meta') );
		return $instance;
	}	

	public static function get_slug(): string {
		return self::POST_TYPE;
	}

	public static function meta_query_filter() {
		add_filter(
			'rest_location_query',
			function ($args, $request) {
			  if ($meta_key = $request->get_param('metaKey')) {
				$args['meta_key'] = $meta_key;
				$args['meta_value'] = $request->get_param('metaValue');
			  }
			  return $args;
			},
			10,
			2
		  );
	}

	public function register_post_type(): void
	{
		$labels = [
			'name' => __('Locations','events'),
			'singular_name' => __('Location','events'),
			'menu_name' => __('Locations','events'),
			'add_new' => __('Add Location','events'),
			'add_new_item' => __('Add New Location','events'),
			'edit' => __('Edit','events'),
			'edit_item' => __('Edit Location','events'),
			'new_item' => __('New Location','events'),
			'view' => __('View','events'),
			'view_item' => __('View Location','events'),
			'search_items' => __('Search Locations','events'),
			'not_found' => __('No Locations Found','events'),
			'not_found_in_trash' => __('No Locations Found in Trash','events'),
			'parent' => __('Parent Location','events'),
		];
		
		$post_type = [	 
			'public' => true,
			'hierarchical' => false,
			'show_in_rest' => true,
			'show_in_admin_bar' => true,
			'show_ui' => true,
			'show_in_menu' => 'edit.php?post_type='.EventPost::POST_TYPE,
			'show_in_nav_menus'=>true,
			'can_export' => true,
			'exclude_from_search' => true,
			'publicly_queryable' => true,
			'rewrite' => ['slug' => EventPost::get_slug(), 'with_front'=>false],
			'query_var' => true,
			'has_archive' => false,
			'template' => [
				['events-manager/locationeditor']
			],
			'supports' => apply_filters('em_cp_location_supports', ['title','excerpt','thumbnail','editor','custom-fields']),
			'label' => __('Locations','events'),
			'description' => __('Display locations on your blog.','events'),
			'labels' => $labels
		];

		register_post_type( self::POST_TYPE, $post_type );
	}


	public function register_meta() : void {
		$meta_array = [
			["_location_address", 'string', ''],
			["_location_town", 'string', ''],
			["_location_state", 'string', ''],
			["_location_postcode", 'string', ''],
			["_location_region", 'string', ''],
			["_location_url", 'string', ''],
			["_location_country", 'string', ''],
			["_location_latitude", "number", 0],
			["_location_longitude", "number", 0]
		];

		foreach($meta_array as $meta) {
			register_post_meta( 'location', $meta[0], [
				'type' => $meta[1],
				'single'       => true,
				'default' => $meta[2],
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
				'show_in_rest' => [
					'schema' => [
						'default' => $meta[2],
						'style' => $meta[1]
					]
				]
			]);
		}
	}
}
if( get_option('dbem_locations_enabled', true) ){
	LocationPost::init();
}