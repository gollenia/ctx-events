<?php

use Contexis\Events\Models\Location;

define('EM_POST_TYPE_EVENT','event'); 
define('EM_POST_TYPE_FORM','bookingform');
define('EM_POST_TYPE_LOCATION','location');
define('EM_TAXONOMY_CATEGORY','event-categories');
define('EM_TAXONOMY_TAG','event-tags');
define('EM_POST_TYPE_ATTENDEEFORM','attendeeform');

define('EM_POST_TYPE_EVENT_SLUG',get_option('dbem_cp_events_slug', 'events'));

// We do not need these slugs in the frontend anymore, but we need to keep their definitions here for the backend
define('EM_POST_TYPE_LOCATION_SLUG','locations');
define('EM_POST_TYPE_FORMS_SLUG','forms');
define('EM_TAXONOMY_CATEGORY_SLUG', 'events/categories');
define('EM_TAXONOMY_TAG_SLUG', 'events/tags');


//This bit registers the CPTs
add_action('init','wp_events_plugin_init',1);
function wp_events_plugin_init(){
	define('EM_ADMIN_URL',admin_url().'edit.php?post_type='.EM_POST_TYPE_EVENT); //we assume the admin url is absolute with at least one querystring
	
	
	
	$event_post_type_supports = apply_filters('em_cp_event_supports', ['title','editor','excerpt','thumbnail','author','custom-fields']);
	
	if ( get_option('dbem_recurrence_enabled') ){
		$event_recurring_post_type = apply_filters('em_cpt_event_recurring', [	
			'public' => apply_filters('em_cp_event_recurring_public', false),
			'show_ui' => true,
			'show_in_rest' => true,
			'show_in_admin_bar' => true,
			'show_in_menu' => 'edit.php?post_type='.EM_POST_TYPE_EVENT,
			'show_in_nav_menus'=>false,
			'publicly_queryable' => apply_filters('em_cp_event_recurring_publicly_queryable', false),
			'exclude_from_search' => true,
			'has_archive' => false,
			'can_export' => true,
			'hierarchical' => false,
			'supports' => $event_post_type_supports,
			'capability_type' => 'recurring_events',
			'rewrite' => ['slug' => 'events-recurring','with_front'=>false],
			'capabilities' => [
				'publish_posts' => 'publish_recurring_events',
				'edit_posts' => 'edit_recurring_events',
				'edit_others_posts' => 'edit_others_recurring_events',
				'delete_posts' => 'delete_recurring_events',
				'delete_others_posts' => 'delete_others_recurring_events',
				'read_private_posts' => 'read_private_recurring_events',
				'edit_post' => 'edit_recurring_event',
				'delete_post' => 'delete_recurring_event',
				'read_post' => 'read_recurring_event',
			],
			'label' => __('Recurring Events','events'),
			'description' => __('Recurring Events Template','events'),
			'labels' => [
				'name' => __('Recurring Events','events'),
				'singular_name' => __('Recurring Event','events'),
				'menu_name' => __('Recurring Events','events'),
				'add_new' => __('Add Recurring Event','events'),
				'add_new_item' => __('Add New Recurring Event','events'),
				'edit' => __('Edit','events'),
				'edit_item' => __('Edit Recurring Event','events'),
				'new_item' => __('New Recurring Event','events'),
				'view' => __('View','events'),
				'view_item' => __('Add Recurring Event','events'),
				'search_items' => __('Search Recurring Events','events'),
				'not_found' => __('No Recurring Events Found','events'),
				'not_found_in_trash' => __('No Recurring Events Found in Trash','events'),
				'parent' => __('Parent Recurring Event','events'),
			]
		]);
	}
	if( get_option('dbem_locations_enabled', true) ){
		$location_post_type = apply_filters('em_cpt_location', [	 
			'public' => true,
			'hierarchical' => false,
			'show_in_rest' => true,
			'show_in_admin_bar' => true,
			'show_ui' => true,
			'show_in_menu' => 'edit.php?post_type='.EM_POST_TYPE_EVENT,
			'show_in_nav_menus'=>true,
			'can_export' => true,
			'exclude_from_search' => true,
			'publicly_queryable' => true,
			'rewrite' => ['slug' => EM_POST_TYPE_LOCATION_SLUG, 'with_front'=>false],
			'query_var' => true,
			'has_archive' => false,
			'template' => [
				['events-manager/locationeditor']
			],
			'supports' => apply_filters('em_cp_location_supports', ['title','excerpt','thumbnail','editor','custom-fields']),
			'capability_type' => 'location',
			'capabilities' => [
				'publish_posts' => 'publish_locations',
				'delete_others_posts' => 'delete_others_locations',
				'delete_posts' => 'delete_locations',
				'delete_post' => 'delete_location',
				'edit_others_posts' => 'edit_others_locations',
				'edit_posts' => 'edit_locations',
				'edit_post' => 'edit_location',
				'read_private_posts' => 'read_private_locations',
				'read_post' => 'read_location',
			],
			'label' => __('Locations','events'),
			'description' => __('Display locations on your blog.','events'),
			'labels' => [
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
			],
		]);
	}

	
	
	function em_gutenberg_support( $can_edit, $post_type ){
		$recurrences = $post_type == 'event-recurring' && get_option('dbem_recurrence_enabled');
		$locations = $post_type == EM_POST_TYPE_LOCATION && get_option('dbem_locations_enabled', true);
		if( $post_type == EM_POST_TYPE_EVENT || $recurrences || $locations ) $can_edit = true;
		return $can_edit;
	}
	add_filter('gutenberg_can_edit_post_type', 'em_gutenberg_support', 10, 2 ); //Gutenberg

	
	if( strstr(EM_POST_TYPE_EVENT_SLUG, EM_POST_TYPE_LOCATION_SLUG) !== false ){
		//Now register posts, but check slugs in case of conflicts and reorder registrations
		if ( get_option('dbem_recurrence_enabled') ){
			register_post_type('event-recurring', $event_recurring_post_type);
		}
		register_post_type(EM_POST_TYPE_LOCATION, $location_post_type);
		
	}else{
		register_post_type(EM_POST_TYPE_LOCATION, $location_post_type);
		//Now register posts, but check slugs in case of conflicts and reorder registrations
		if ( get_option('dbem_recurrence_enabled') ){
			register_post_type('event-recurring', $event_recurring_post_type);
		}
	}

}



function em_map_meta_cap( $caps, $cap, $user_id, $args ) {
    if( !empty( $args[0]) ){
		/* Handle event reads */
		if ( 'edit_event' == $cap || 'delete_event' == $cap || 'read_event' == $cap ) {
			$post = get_post($args[0]);
			//check for revisions and deal with non-event post types
			if( !empty($post->post_type) && $post->post_type == 'revision' ) $post = get_post($post->post_parent);
			if( empty($post->post_type) || !in_array($post->post_type, array(EM_POST_TYPE_EVENT, 'event-recurring')) ) return $caps;
			//continue with getting post type and assigning caps
			$event = \Contexis\Events\Models\Event::find_by_post($post);
			$post_type = get_post_type_object( $event->post_type );
			/* Set an empty array for the caps. */
			$caps = [];
			//Filter according to event caps
			switch( $cap ){
				case 'read_event':
					if ( 'private' != $event->post_status )
						$caps[] = 'read';
					elseif ( $user_id == $event->event_owner )
						$caps[] = 'read';
					else
						$caps[] = $post_type->cap->read_private_posts;
					break;
				case 'edit_event':
					if ( $user_id == $event->event_owner )
						$caps[] = $post_type->cap->edit_posts;
					else
						$caps[] = $post_type->cap->edit_others_posts;
					break;
				case 'delete_event':
					if ( $user_id == $event->event_owner )
						$caps[] = $post_type->cap->delete_posts;
					else
						$caps[] = $post_type->cap->delete_others_posts;
					break;
			}
		}
		if ( 'edit_recurring_event' == $cap || 'delete_recurring_event' == $cap || 'read_recurring_event' == $cap ) {
			$post = get_post($args[0]);
			//check for revisions and deal with non-event post types
			if( !empty($post->post_type) && $post->post_type == 'revision' ) $post = get_post($post->post_parent);
			if( empty($post->post_type) || $post->post_type != 'event-recurring' ) return $caps;
			//continue with getting post type and assigning caps
			$event = \Contexis\Events\Models\Event::find_by_post($post);
			$post_type = get_post_type_object( $event->post_type );
			/* Set an empty array for the caps. */
			$caps = [];
			//Filter according to recurring_event caps
			switch( $cap ){
				case 'read_recurring_event':
					if ( 'private' != $event->post_status )
						$caps[] = 'read';
					elseif ( $user_id == $event->event_owner )
						$caps[] = 'read';
					else
						$caps[] = $post_type->cap->read_private_posts;
					break;
				case 'edit_recurring_event':
					if ( $user_id == $event->event_owner )
						$caps[] = $post_type->cap->edit_posts;
					else
						$caps[] = $post_type->cap->edit_others_posts;
					break;
				case 'delete_recurring_event':
					if ( $user_id == $event->event_owner )
						$caps[] = $post_type->cap->delete_posts;
					else
						$caps[] = $post_type->cap->delete_others_posts;
					break;
			}
		}
		if ( 'edit_location' == $cap || 'delete_location' == $cap || 'read_location' == $cap ) {
			$post = get_post($args[0]);
			//check for revisions and deal with non-location post types
			if( !empty($post->post_type) && $post->post_type == 'revision' ) $post = get_post($post->post_parent);
			if( empty($post->post_type) || $post->post_type != EM_POST_TYPE_LOCATION ) return $caps;
			//continue with getting post type and assigning caps
			$location = Location::find_by_post($post);
			$post_type = get_post_type_object( $location->post_type );
			/* Set an empty array for the caps. */
			$caps = [];
			//Filter according to location caps
			switch( $cap ){
				case 'read_location':
					if ( 'private' != $location->post_status )
						$caps[] = 'read';
					elseif ( $user_id == $location->location_owner )
						$caps[] = 'read';
					else
						$caps[] = $post_type->cap->read_private_posts;
					break;
				case 'edit_location':
					if ( $user_id == $location->location_owner )
						$caps[] = $post_type->cap->edit_posts;
					else
						$caps[] = $post_type->cap->edit_others_posts;
					break;
				case 'delete_location':
					if ( $user_id == $location->location_owner )
						$caps[] = $post_type->cap->delete_posts;
					else
						$caps[] = $post_type->cap->delete_others_posts;
					break;
			}
		}
    }
	/* Return the capabilities required by the user. */
	return $caps;
}
add_filter( 'map_meta_cap', 'em_map_meta_cap', 10, 4 );