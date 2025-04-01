<?php

namespace Contexis\Events\PostTypes;

use Contexis\Events\Collections\EventCollection;
use WP_Query;

class EventPost {

	const POST_TYPE = "event";
	
	public static function init(){

		$instance = new self;
		add_action('init', array($instance, 'register_post_type'));
		add_action('init', array($instance, 'register_taxonomies'));
		add_action('parse_query', array($instance,'parse_query'));
		add_action('rest_api_init', array($instance, 'register_meta'));	
	}	

	public function register_taxonomies() {
		register_taxonomy(EM_TAXONOMY_TAG,[EM_POST_TYPE_EVENT,'event-recurring'], apply_filters('em_ct_tags', [
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_rest' => true,
			'query_var' => true, 
			'rewrite' => ['slug' => EM_TAXONOMY_TAG_SLUG,'with_front'=>false],
			'label' => __('Event Tags'),
			'show_admin_column' => true,
			'singular_label' => __('Event Tag'),
			'labels' => [
				'name'=>__('Event Tags','events'),
				'singular_name'=>__('Event Tag','events'),
				'search_items'=>__('Search Event Tags','events'),
				'popular_items'=>__('Popular Event Tags','events'),
				'all_items'=>__('All Event Tags','events'),
				'parent_items'=>__('Parent Event Tags','events'),
				'parent_item_colon'=>__('Parent Event Tag:','events'),
				'edit_item'=>__('Edit Event Tag','events'),
				'update_item'=>__('Update Event Tag','events'),
				'add_new_item'=>__('Add New Event Tag','events'),
				'new_item_name'=>__('New Event Tag Name','events'),
				'separate_items_with_commas'=>__('Separate event tags with commas','events'),
				'add_or_remove_items'=>__('Add or remove events','events'),
				'choose_from_the_most_used'=>__('Choose from most used event tags','events'),
			],
			'capabilities' => [
				'manage_terms' => 'edit_event_categories',
				'edit_terms' => 'edit_event_categories',
				'delete_terms' => 'delete_event_categories',
				'assign_terms' => 'edit_events',
			]
		]));
	
		register_taxonomy(EM_TAXONOMY_CATEGORY,[EM_POST_TYPE_EVENT,'event-recurring'], apply_filters('em_ct_categories', [
			'hierarchical' => true,
			'public' => true,
			'show_ui' => true,
			'show_in_rest' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => ['slug' => EM_TAXONOMY_CATEGORY_SLUG, 'hierarchical' => true,'with_front'=>false],
			'show_in_nav_menus' => true,
			'label' => __('Event Categories','events'),
			'singular_label' => __('Event Category','events'),
			'labels' => [
				'name'=>__('Event Categories','events'),
				'singular_name'=>__('Event Category','events'),
				'search_items'=>__('Search Event Categories','events'),
				'popular_items'=>__('Popular Event Categories','events'),
				'all_items'=>__('All Event Categories','events'),
				'parent_items'=>__('Parent Event Categories','events'),
				'parent_item_colon'=>__('Parent Event Category:','events'),
				'edit_item'=>__('Edit Event Category','events'),
				'update_item'=>__('Update Event Category','events'),
				'add_new_item'=>__('Add New Event Category','events'),
				'new_item_name'=>__('New Event Category Name','events'),
				'separate_items_with_commas'=>__('Separate event categories with commas','events'),
				'add_or_remove_items'=>__('Add or remove events','events'),
				'choose_from_the_most_used'=>__('Choose from most used event categories','events'),
			],
			'capabilities' => [
				'manage_terms' => 'edit_event_categories',
				'edit_terms' => 'edit_event_categories',
				'delete_terms' => 'delete_event_categories',
				'assign_terms' => 'edit_events',
			]
		]));
	}

	public function register_post_type() {
		$labels = [
			'name' => __('Events','events'),
			'singular_name' => __('Event','events'),
			'menu_name' => __('Events','events'),
			'add_new_item' => __('Add New Event','events'),
			'edit' => __('Edit','events'),
			'edit_item' => __('Edit Event','events'),
			'view' => __('View','events'),
			'view_item' => __('View Event','events'),
			'search_items' => __('Search Events','events'),
			'not_found' => __('No Events Found','events'),
			'not_found_in_trash' => __('No Events Found in Trash','events'),
			'parent' => __('Parent Event','events'),
		];

		$event_post_type = [	
			'public' => true,
			'hierarchical' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
			'show_in_nav_menus'=>true,
			'can_export' => true,
			'exclude_from_search' => !get_option('dbem_cp_events_search_results'),
			'publicly_queryable' => true,
			'rewrite' => ['slug' => EM_POST_TYPE_EVENT_SLUG,'with_front'=>false],
			'has_archive' => false,
			'supports' => ['title','editor','excerpt','thumbnail','author','custom-fields'],
			'template' => [
					['ctx-blocks/grid-row', [], [
						['ctx-blocks/grid-column', ['widthLarge' => 2], [['core/paragraph', ['placeholder' => 'Event-Beschreibung']],]],
						['ctx-blocks/grid-column', ['widthLarge' => 1], [
							['events-manager/details', []],
						]]
					]],
					['core/separator'],
					['core/group', ['layout' => ['type' => 'flex', 'flexWrap' => 'nowrap', 'justifyContent' => 'right']], [['events-manager/booking', ['title' => 'Anmeldung']]]]
			],
			'capability_type' => 'event',
			'capabilities' => [
				'publish_posts' => 'publish_events',
				'edit_posts' => 'edit_events',
				'edit_others_posts' => 'edit_others_events',
				'delete_posts' => 'delete_events',
				'delete_others_posts' => 'delete_others_events',
				'read_private_posts' => 'read_private_events',
				'edit_post' => 'edit_event',
				'delete_post' => 'delete_event',
				'read_post' => 'read_event',		
			],
			'label' => __('Events','events'),
			'description' => __('Display events on your blog.','events'),
			'labels' => $labels,
			//'rest_controller_class' => '\Contexis\Events\Events\EventRestController',
			'menu_icon' => 'dashicons-calendar-alt'
		];
		
		register_post_type( 'event', $event_post_type );
	}

	public function register_meta() {

		$metadata = json_decode(file_get_contents(__DIR__ . '/metadata.json'), true);

		foreach($metadata as $meta){
			if(!in_array('event', $meta['post_type'])){
				continue;
			}
			register_post_meta( 'event', $meta['name'], [
				'type' => $meta['type'],
				'single'       => true,
				
				'sanitize_callback' => '',
				'auth_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
				'show_in_rest' => [
					'schema' => [	
						'style' => $meta['type']
					]
				]
			]);
		}

		foreach($metadata as $meta){
			if(!in_array('event-recurring', $meta['post_type'])){
				continue;
			}
			register_post_meta( 'event-recurring', $meta['name'], [
				'type' => $meta['type'],
				'single'       => true,
				'sanitize_callback' => '',
				'auth_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
				'show_in_rest' => [
					'schema' => [
						'style' => $meta['type']
					]
				]
			]);
		}
	

	}
	
	public static function parse_query(WP_Query $query) : WP_Query{
		if(!isset($query->query_vars['post_type'])){
			return $query;
		}
		if( $query->query_vars['post_type'] != 'event' && $query->query_vars['post_type'] != 'event-recurring' ){
			return $query;
		}
		
		if( !$query->is_main_query() ){
			return $query;
		}
		$args = [];
		$args['scope'] = (!empty($_REQUEST['scope'])) ? $_REQUEST['scope']:'future';
		if(!empty($_REQUEST['orderby'])) $args['orderby'] = $_REQUEST['orderby'];
		if(!empty($_REQUEST['order'])) $args['order'] = $_REQUEST['order'];
		if(!empty($_REQUEST['event-categories'])) $args['event-categories'] = $_REQUEST['event-categories'];
		

		$args = EventCollection::get_query_args($args);
		$query->query_vars = array_merge($query->query_vars, $args);
		\Contexis\Events\Utilities::object_to_js_console($query->query_vars);
		return $query;
		
	}

}
EventPost::init();