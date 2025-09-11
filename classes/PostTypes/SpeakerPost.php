<?php

namespace Contexis\Events\PostTypes;

use Contexis\Events\Core\Contracts\PostType;

class SpeakerPost implements PostType {

	const POST_TYPE = 'event-speaker';

	public static function init() : self {
		$instance = new self;
		add_action('init', array($instance, 'register_post_type'));
		add_action('init', array($instance, 'register_meta'));
		return $instance;
	}
	public static function get_slug(): string {
		return self::POST_TYPE;
	}

	public function register_post_type() : void {
		$args = apply_filters('em_cpt_speaker', [	
            'public' => false,
            'hierarchical' => false,
            'show_in_rest' => true,
            'show_in_admin_bar' => true,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=event',
            'show_in_nav_menus'=>true,
            'can_export' => true,
            'publicly_queryable' => false,
            'rewrite' => ['slug' => 'event-speaker', 'with_front'=>false],
            'query_var' => false,
            'has_archive' => false,
            'supports' => ['title', 'thumbnail', 'editor', 'excerpt', 'custom-fields'],
            'label' => __('Speakers','events'),
            'description' => __('Speakers for an event.','events'),
            'labels' => [
                'name' => __('Speakers','events'),
                'singular_name' => __('Speaker','events'),
                'menu_name' => __('Speakers','events'),
                'add_new' => __('Add Speaker','events'),
                'add_new_item' => __('Add New Speaker','events'),
                'edit' => __('Edit','events'),
                'edit_item' => __('Edit Speaker','events'),
                'new_item' => __('New Speaker','events'),
                'view' => __('View','events'),
                'view_item' => __('View Speaker','events'),
                'search_items' => __('Search Speaker','events'),
                'not_found' => __('No Speaker Found','events'),
                'not_found_in_trash' => __('No Speaker Found in Trash','events'),
                'parent' => __('Parent Speaker','events'),
            ],
        ]);

		register_post_type( SELF::POST_TYPE, $args );     
    }

	public function register_meta() :void {
		
		register_post_meta( 'event-speaker', '_email', [
			'type' => 'string',
			'show_in_rest' => [
				'schema' => [
					'default' => '',
					'type' => "string"
				]
				],
			'single'       => true,
			'default'      => '',
			'auth_callback' => function() {
				return current_user_can( 'edit_posts' );
			}
		]);

		register_post_meta('event-speaker', '_phone', [
			'type' => 'string',
			'show_in_rest' => [
				'schema' => [
					'default' => '',
					'type' => "string"
				]
				],
			'single'       => true,
			'default'      => '',
			'auth_callback' => function() {
				return current_user_can( 'edit_posts' );
			}
		]);

		register_post_meta('event-speaker', '_gender', [
			'type' => 'string',
			'show_in_rest' => [
				'schema' => [
					'default' => '',
					'type' => "string"
				]
				],
			'single'       => true,
			'default'      => 'male',
			'auth_callback' => function() {
				return current_user_can( 'edit_posts' );
			}
		]);

		register_post_meta('event-speaker', '_role', [
			'type' => 'string',
			'show_in_rest' => [
				'schema' => [
					'default' => '',
					'type' => "string"
				]
				],
			'single'       => true,
			'default'      => '',
			'auth_callback' => function() {
				return current_user_can( 'edit_posts' );
			}
		]);

		register_rest_field( 'event-speaker', 'meta', [
			'get_callback' => function($object) {
				$meta = get_post_meta($object['id']);
				$meta['thumbnail'] = get_the_post_thumbnail_url($object['id']);
				return $meta;
			},
			'schema' => null,
		]);

	}
}
