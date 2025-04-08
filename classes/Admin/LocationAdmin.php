<?php

use Contexis\Events\Models\Location;
use Contexis\Events\PostTypes\EventPost;
use Contexis\Events\PostTypes\LocationPost;

class LocationAdmin {
	public static function init(){
		global $pagenow;
		if($pagenow == 'edit.php' && !empty($_REQUEST['post_type']) && $_REQUEST['post_type'] == LocationPost::POST_TYPE ){ //only needed if editing post
			//hide some cols by default:
			$screen = 'edit-'.LocationPost::POST_TYPE;
			$hidden = get_user_option( 'manage' . $screen . 'columnshidden' );
			if( $hidden === false ){
				$hidden = array('location-id');
				update_user_option(get_current_user_id(), "manage{$screen}columnshidden", $hidden, true);
			}
			add_action('admin_head', array('LocationAdmin','admin_head'));
		}
		add_filter('manage_'.LocationPost::POST_TYPE.'_posts_columns' , array('LocationAdmin','columns_add'));
		add_filter('manage_'.LocationPost::POST_TYPE.'_posts_custom_column' , array('LocationAdmin','columns_output'),10,2 );
		add_filter('manage_edit-'.LocationPost::POST_TYPE.'_sortable_columns', array('LocationAdmin','columns_sortable'));
			
	}
	
	public static function admin_head(){
		//quick hacks to make event admin table make more sense for events
		?>
		<script type="text/javascript">
			jQuery(document).ready( function($){
				$('.inline-edit-date').prev().css('display','none').next().css('display','none').next().css('display','none');
			});
		</script>
		<style>
			table.fixed{ table-layout:auto !important; }
			.tablenav select[name="m"] { display:none; }
		</style>
		<?php
	}
	
	public static function admin_menu(){
		global $menu, $submenu;
	  	// Add a submenu to the custom top-level menu:
   		$plugin_pages = array(); 
		$plugin_pages[] = add_submenu_page('edit.php?post_type='.EventPost::POST_TYPE, __('Locations', 'events'), __('Locations', 'events'), 'edit_locations', 'events-locations', "edit.php?post_type=event");
		$plugin_pages = apply_filters('em_create_locationss_submenu',$plugin_pages);
	}
	
	public static function columns_add($columns) {
		//prepend ID after checkbox
		if( array_key_exists('cb', $columns) ){
			$cb = $columns['cb'];
	    	unset($columns['cb']);
	    	$id_array = array('cb'=>$cb, 'location-id' => sprintf(__('%s ID','events'),__('Location','events')));
		}else{
	    	$id_array = array('location-id' => sprintf(__('%s ID','events'),__('Location','events')));
		}
	    unset($columns['author']);
	    unset($columns['date']);
	    unset($columns['comments']);
	    return array_merge($id_array, $columns, array(
	    	'address' => __('Address','events'), 
	    	'town' => __('Town','events'),
	    	'zip' => __('Postcode','events'),
	    	'country' => __('Country','events') 
	    ));
	}
	
	public static function columns_output( $column ) {
		$location = Location::find_by_post(get_post()); 
		switch ( $column ) {
			case 'location-id':
				echo $location->location_id;
				break;
			case 'address':
				echo $location->location_address;
				break;
			case 'town':
				echo $location->location_town;
				break;
			case 'zip':
				echo $location->location_postcode;
				break;
			case 'country':
				echo $location->location_country;
				break;
		}
	}

	public static function columns_sortable( $columns ) { 
	   $columns['address'] = 'address';
	   $columns['town'] = 'town';
	   $columns['zip'] = 'zip';
	   $columns['country'] = 'country';
	   return $columns;
	}
 
}
add_action('admin_init', array('LocationAdmin','init'));