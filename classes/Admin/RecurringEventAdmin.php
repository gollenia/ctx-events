<?php

namespace Contexis\Events\Admin;
use Contexis\Events\Models\Event;
use Contexis\Events\Collections\EventCollection;
use Contexis\Events\PostTypes\EventPost;


class RecurringEventAdmin {
	public static function init(){
		if(!is_admin()) return;
		$instance = new self;
		
		
		add_action('init', array($instance,'load_user_options'), 10, 1);
		add_action('admin_head', array($instance,'admin_head'));
		//Save/Edit actions
		
		add_filter('manage_event-recurring_posts_columns' , array($instance,'columns_add'));
		add_filter('manage_event-recurring_posts_custom_column' , array($instance,'columns_output'),10,1 );
		//add_action('restrict_manage_posts', array($instance,'restrict_manage_posts'));
		add_filter( 'manage_edit-event-recurring_sortable_columns', array($instance,'sortable_columns') );

		//Notices
		add_action('post_updated_messages',array('EM_Event_Post_Admin','admin_notices_filter'),1,1); //shared with posts
	}
	
	public static function load_user_options($post_type){
		$screen = 'edit-event-recurring';
		$hidden = get_user_option( 'manage' . $screen . 'columnshidden' );
		if( $hidden === false ){
			$hidden = array('event-id');
			update_user_option(get_current_user_id(), "manage{$screen}columnshidden", $hidden, true);
		}
	}

	public static 	function before_delete_post($post_id){
		if(get_post_type($post_id) == 'event-recurring'){
			$event = Event::find_by_post_id($post_id);
			do_action('em_event_delete_pre ',$event);
			//now delete recurrences
			//only delete other events if this isn't a draft-never-published event
			if( !empty($event->event_id) ){
    			$events_array = EventCollection::find( array('recurrence'=>$event->event_id, 'scope'=>'all', 'status'=>'everything' ) );
    			foreach($events_array as $event){
    				/* @var $event Event */
    				if($event->event_id == $event->recurrence_id && !empty($event->recurrence_id) ){ //double check the event is a recurrence of this event
    					wp_delete_post($event->post_id, true);
    				}
    			}
			}
			$event->post_type = EventPost::POST_TYPE; //trick it into thinking it's one event.
			$event->delete_meta();
		}
	}
	
	public static function trashed_post($post_id){
		if(get_post_type($post_id) == 'event-recurring'){
			$event = Event::find_by_post_id($post_id);
			//only trash other events if this isn't a draft-never-published event
			if( !empty($event->event_id) ){
    			//now trash recurrences
    			$events_array = EventCollection::find( array('recurrence_id'=>$event->event_id, 'scope'=>'all', 'status'=>'everything' ) );
    			foreach($events_array as $event){
    				/* @var $event Event */
    				if($event->event_id == $event->recurrence_id ){ //double check the event is a recurrence of this event
    					wp_trash_post($event->post_id);
    				}
    			}
			}
		}
	}
	
	public static function untrash_post($post_id){
		if(get_post_type($post_id) == 'event-recurring'){
			//set a constant so we know this event doesn't need 'saving'
			if(!defined('UNTRASHING_'.$post_id)) define('UNTRASHING_'.$post_id, true);
			$event = Event::find_by_post_id($post_id);
			//only untrash other events if this isn't a draft-never-published event, because if so it never had other events to untrash
			if( !empty($event->event_id) ){
    			$events_array = EventCollection::find( array('recurrence_id'=>$event->event_id, 'scope'=>'all', 'status'=>'everything' ) );
    			foreach($events_array as $event){
    				/* @var $event Event */
    				if($event->event_id == $event->recurrence_id){
    					wp_untrash_post($event->post_id);
    				}
    			}
			}
		}
	}

	
	public static function meta_boxes( $post ){
		$event = Event::find_by_post($post) ?? new Event();
		if( get_option('dbem_rsvp_enabled') && current_user_can('edit_posts') ){
			add_meta_box('em-event-bookings', __('Bookings/Registration','events'), array('EM_Event_Post_Admin','meta_box_bookings'),'event-recurring', 'normal','high');
		}
	}
	
	
	public static function columns_add($columns) {
		if( array_key_exists('cb', $columns) ){
			$cb = $columns['cb'];
	    	unset($columns['cb']);
	    	$id_array = array('cb'=>$cb, 'event-id' => sprintf(__('%s ID','events'),__('Event','events')));
		}else{
	    	$id_array = array('event-id' => sprintf(__('%s ID','events'),__('Event','events')));
		}
	    unset($columns['comments']);
	    unset($columns['date']);
	    unset($columns['author']);
	    $columns = array_merge($id_array, $columns, array(
	    	'location' => __('Location','events'),
	    	'date-time' => __('Date and Time','events'),
	    	'author' => __('Owner','events'),
	    ));
		if( !get_option('dbem_locations_enabled', 1) ){
			unset($columns['location']);
		}
		return $columns;
	}

	
	public static function columns_output( $column ) {
		global $post;
		if( $post->post_type == 'event-recurring' ){
			$event = Event::find_by_post($post);
			/* @var $post Event */
			switch ( $column ) {
				case 'event-id':
					echo $event->event_id;
					break;
				case 'location':
					//get meta value to see if post has location, otherwise
					$location = $event->get_location();
					if( !empty($location->location_id) ){
						echo "<strong><a href='". esc_url($location->get_edit_url())."'>" . $location->location_name . "</a></strong>";
						echo "<br/>" . $location->location_address . " - " . $location->location_town;
					}else{
						echo __('None','events');
					}
					break;
				case 'date-time':
					echo $event->get_recurrence_description();
					$edit_url = add_query_arg(array('scope'=>'all', 'recurrence_id'=>$event->event_id), admin_url('edit.php?post_type=event'));
					$link_text = sprintf(__('View %s', 'events'), __('Recurrences', 'events'));
					echo "<br /><span class='row-actions'>
							<a href='". esc_url($edit_url) ."'>". esc_html($link_text) ."</a>
						</span>";
					break;
			}
		}
	}
	
	public static function row_actions($actions, $post){
		if($post->post_type == 'event-recurring'){
			$event = Event::find_by_post($post);
			unset($actions['inline hide-if-no-js']);
			$actions['duplicate'] = '<a href="'.$event->duplicate_url().'" title="'.sprintf(__('Duplicate %s','events'), __('Event','events')).'">'.__('Duplicate','events').'</a>';
		}
		return $actions;
	}

	public static function admin_head(){
		//quick hacks to make event admin table make more sense for events
		?>
		
		<style>
			table.fixed{ table-layout:auto !important; }
			.tablenav select[name="m"] { display:none; }
		</style>
		<?php
	}
	

}
RecurringEventAdmin::init();