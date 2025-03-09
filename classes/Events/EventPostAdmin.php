<?php
/*
 * Events Edit Page
 */
class EM_Event_Post_Admin{
	public static function init(){
		global $pagenow;
		if($pagenow == 'post.php' || $pagenow == 'post-new.php' ){ //only needed if editing post
			add_action('admin_head', array('EM_Event_Post_Admin','admin_head')); //I don't think we need this anymore?
			//Meta Boxes
			add_action('add_meta_boxes_'.EM_POST_TYPE_EVENT, array('EM_Event_Post_Admin','meta_boxes'), 10, 1);
			//Notices
			add_action('admin_notices',array('EM_Event_Post_Admin','admin_notices'));
		}
		//Save/Edit actions
		add_filter('wp_insert_post_data',array('EM_Event_Post_Admin','wp_insert_post_data'),100,2); //validate post meta before saving is done
		add_action('save_post',array('EM_Event_Post_Admin','save_post'),10,3); //set to 1 so metadata gets saved ASAP
		add_action('before_delete_post',array('EM_Event_Post_Admin','before_delete_post'),10,1);
		add_action('trashed_post',array('EM_Event_Post_Admin','trashed_post'),10,1);
		add_action('untrash_post',array('EM_Event_Post_Admin','untrash_post'),10,1);
		add_action('untrashed_post',array('EM_Event_Post_Admin','untrashed_post'),10,1);
		//Notices
		add_action('post_updated_messages',array('EM_Event_Post_Admin','admin_notices_filter'),1,1);
	}

	public static function admin_head(){
		global $post, $EM_Event;
		if( empty($EM_Event) && !empty($post) && $post->post_type == EM_POST_TYPE_EVENT ){
			$EM_Event = EM_Event::find_by_post($post);
		}
	}
	
	public static function admin_notices(){
		//When editing
		global $post, $EM_Event, $pagenow;
		if( $pagenow == 'post.php' && ($post->post_type == EM_POST_TYPE_EVENT || $post->post_type == 'event-recurring') ){
			if ( $EM_Event->is_recurring() ) {
				$warning = "<p><strong>".__( 'WARNING: This is a recurring event.', 'events')."</strong></p>";
				$warning .= "<p>". __( 'Modifications to recurring events will be applied to all recurrences and will overwrite any changes made to those individual event recurrences.', 'events') . '</p>';
				$warning .= "<p>". __( 'Bookings to individual event recurrences will be preserved if event times and ticket settings are not modified.', 'events') . '</p>';
				$warning .= '<p><a href="'. esc_url( add_query_arg(array('scope'=>'all', 'recurrence_id'=>$EM_Event->event_id), admin_url('edit.php?post_type=event')) ).'">'. esc_html__('You can edit individual recurrences and disassociate them with this recurring event.','events') . '</a></p>';
				?><div class="notice notice-warning is-dismissible"><?php echo $warning; ?></div><?php
			} elseif ( $EM_Event->is_recurrence() ) {
				$warning = "<p><strong>".__('WARNING: This is a recurrence in a set of recurring events.', 'events')."</strong></p>";
				$warning .= "<p>". sprintf(__('If you update this event data and save, it could get overwritten if you edit the recurring event template. To make it an independent, <a href="%s">detach it</a>.', 'events'), $EM_Event->get_detach_url())."</p>";
				$warning .= "<p>".sprintf(__('To manage the whole set, <a href="%s">edit the recurring event template</a>.', 'events'),admin_url('post.php?action=edit&amp;post='.$EM_Event->get_event_recurrence()->post_id))."</p>";
				?><div class="notice notice-warning is-dismissible"><?php echo $warning; ?></div><?php
			}
			
		}
	}
	
	public static function admin_notices_filter($messages){
		//When editing
		global $post, $EM_Notices; /* @var EM_Notices $EM_Notices */
		if( $post->post_type == EM_POST_TYPE_EVENT || $post->post_type == 'event-recurring' ){
			if ( $EM_Notices->count_errors() > 0 ) {
				unset($_GET['message']);
			}
		}
		return $messages;
	}
	
	/**
	 * Validate event once BEFORE it goes into the database, because otherwise it could get 'published' between now and save_post, 
	 * allowing other plugins hooking here to perform incorrect actions e.g. tweet a new event.
	 *  
	 * @param array $data
	 * @param array $postarr
	 * @return array
	 */
	public static function wp_insert_post_data( $data, $postarr ){
		global $wpdb, $EM_SAVING_EVENT;
		if( !empty($EM_SAVING_EVENT) ) return $data; //never proceed with this if using EM_Event::save();
		$post_type = $data['post_type'];
		$post_ID = !empty($postarr['ID']) ? $postarr['ID'] : false;
		$is_post_type = $post_type == EM_POST_TYPE_EVENT || $post_type == 'event-recurring';
		$doing_add_meta_ajax = defined('DOING_AJAX') && DOING_AJAX && !empty($_REQUEST['action']) && $_REQUEST['action'] == 'add-meta' && check_ajax_referer( 'add-meta', '_ajax_nonce-add-meta', false );  //we don't need to save anything here, we don't use this action
		$saving_status = !in_array($data['post_status'], array('trash','auto-draft')) && !defined('DOING_AUTOSAVE') && !$doing_add_meta_ajax;
		$untrashing = $post_ID && defined('UNTRASHING_'.$post_ID);
		if( !$untrashing && $is_post_type && $saving_status ){
			if( true ){ 
				//this is only run if we know form data was submitted, hence the nonce
				$EM_Event = EM_Event::find_by_post_id($post_ID);
				$EM_Event->post_type = $post_type;
				//Handle Errors by making post draft
				$get_meta = $EM_Event->get_post_meta();
				$validate_meta = $EM_Event->validate_meta();
				if( !$get_meta || !$validate_meta ) $data['post_status'] = 'draft';
			}
		}
		return $data;
	}
	
	public static function save_post(int $post_id, WP_Post $post){
		global $EM_Notices; /* @var EM_Notices $EM_Notices */

		if ( isset($_GET['preview_id']) && isset($_GET['preview_nonce']) && wp_verify_nonce( $_GET['preview_nonce'], 'post_preview_' . $post_id ) ) return; 

		$post_type = get_post_type($post);
		$is_post_type = $post_type == \Contexis\Events\Events\EventPost::POST_TYPE || $post_type == 'event-recurring';
		$saving_status = !in_array(get_post_status($post_id), array('trash','auto-draft')) && !defined('DOING_AUTOSAVE');
		if(defined('UNTRASHING_'.$post_id) || !$is_post_type || !$saving_status) return;
		
		$EM_Event = EM_Event::find_by_post_id($post_id);
		
		$get_meta = $EM_Event->get_post_meta();
		$validate_meta = $EM_Event->validate_meta(); //Handle Errors by making post draft
		$save_meta = $EM_Event->save_meta();
		$EM_Event->get_categories()->save();

		if( !$get_meta || !$validate_meta || !$save_meta ){
			//failed somewhere, set to draft, don't publish
			$EM_Event->set_status(null, true);
			if( $EM_Event->is_recurring() ){
				$EM_Notices->add_error( '<strong>'.__('Your event details are incorrect and recurrences cannot be created, please correct these errors first:','events').'</strong>', true); //Always seems to redirect, so we make it static
			}else{
				$EM_Notices->add_error( '<strong>'.sprintf(__('Your %s details are incorrect and cannot be published, please correct these errors first:','events'),__('event','events')).'</strong>', true); //Always seems to redirect, so we make it static
			}
			$EM_Notices->add_error($EM_Event->get_errors(), true); //Always seems to redirect, so we make it static
			apply_filters('em_event_save', false, $EM_Event);
		}else{
			//if this is just published, we need to email the user about the publication, or send to pending mode again for review
			if( (!$EM_Event->is_recurring() && !current_user_can('publish_events')) || ($EM_Event->is_recurring() && !current_user_can('publish_recurring_events')) ){
				if( $EM_Event->is_published() ){ $EM_Event->set_status(0, true); } //no publishing and editing... security threat
			}
			apply_filters('em_event_save', true, $EM_Event);
			//flag a cache refresh if we get here

			add_filter('save_post', 'EM_Event_Post_Admin::refresh_cache', 10, 2);
		}
		
		self::maybe_publish_location($EM_Event);
		
	}
	
	public static function refresh_cache(int $post_id = 0) { 
		error_log($post_id);
		$event = EM_Event::find_by_post_id($post_id);
		if (!$event || empty($event->refresh_cache) || empty($event->post_id) || !$event->is_published()) {
			return;
		}
	
		$post = get_post($event->post_id);
		$event->load_postdata($post);
		unset($event->refresh_cache);
	
		wp_cache_set($event->event_id, $event, 'em_events');
		wp_cache_set($event->post_id, $event->event_id, 'em_events_ids');
	}
	

	public static function maybe_publish_location($EM_Event){
		//do a dirty update for location too if it's not published
		if( $EM_Event->is_published() && !empty($EM_Event->location_id) ){
			$EM_Location = $EM_Event->get_location();
			
		}
	}

	public static function before_delete_post($post_id){
		if(get_post_type($post_id) == EM_POST_TYPE_EVENT){
			$EM_Event = EM_Event::find_by_post_id($post_id);
			do_action('em_event_delete_pre ',$EM_Event);
			$EM_Event->delete_meta();
		}
	}
	
	public static function trashed_post($post_id){
		if(get_post_type($post_id) == EM_POST_TYPE_EVENT){
			$EM_Event = EM_Event::find_by_post_id($post_id);
			$EM_Event->set_status(-1);
		}
	}
	
	public static function untrash_post($post_id){
		if(get_post_type($post_id) == EM_POST_TYPE_EVENT){
			//set a constant so we know this event doesn't need 'saving'
			if(!defined('UNTRASHING_'.$post_id)) define('UNTRASHING_'.$post_id, true);
		}
	}
	
	public static function untrashed_post($post_id){
		if(get_post_type($post_id) == EM_POST_TYPE_EVENT){
			$EM_Event = EM_Event::find_by_post_id($post_id);
			$EM_Event->set_status( $EM_Event->get_status() );
		}
	}
	
	public static function meta_boxes($post) {
		$event = EM_Event::find_by_post($post);
	
		if (get_option('dbem_rsvp_enabled', true) && $event->can_manage('manage_bookings','manage_others_bookings')) {
			add_meta_box(
				'em-event-bookings',
				__('Bookings/Registration', 'events'),
				array('EM_Event_Post_Admin', 'meta_box_bookings'),
				EM_POST_TYPE_EVENT,
				'normal',
				'high'
			);
		}
	}
	
	public static function meta_box_date(){
		//create meta box check of date nonce
		?><input type="hidden" name="_emnonce" value="<?php echo wp_create_nonce('edit_event'); ?>" /><?php
		
	}


	public static function meta_box_bookings($post){
		$event = EM_Event::find_by_post($post);
		echo '<div id="event-rsvp-options">';
	
			do_action('em_events_admin_bookings_footer', $event); 
		
		echo "</div>";
	}
}
add_action('admin_init',array('EM_Event_Post_Admin','init'));

/*
 * Recurring Events
 */
class EM_Event_Recurring_Post_Admin{
	public static function init(){
		global $pagenow;
		if($pagenow == 'post.php' || $pagenow == 'post-new.php' ){ //only needed if editing post
			add_action('admin_head', array('EM_Event_Recurring_Post_Admin','admin_head'));
			//Meta Boxes
			add_action('add_meta_boxes_event-recurring', array('EM_Event_Recurring_Post_Admin','meta_boxes'), 10, 1);
			//Notices
			add_action('admin_notices',array('EM_Event_Post_Admin','admin_notices')); //shared with posts
		}
		//Save/Edit actions
		add_action('save_post',array('EM_Event_Recurring_Post_Admin','save_post'),10000,1); //late priority for checking non-EM meta data added later
		add_action('before_delete_post',array('EM_Event_Recurring_Post_Admin','before_delete_post'),10,1);
		add_action('trashed_post',array('EM_Event_Recurring_Post_Admin','trashed_post'),10,1);
		add_action('untrash_post',array('EM_Event_Recurring_Post_Admin','untrash_post'),10,1);
		add_action('untrashed_post',array('EM_Event_Recurring_Post_Admin','untrashed_post'),10,1);
		//Notices
		add_action('post_updated_messages',array('EM_Event_Post_Admin','admin_notices_filter'),1,1); //shared with posts
	}
	
	public static function admin_head(){
		global $post, $EM_Event;
		if( !empty($post) && $post->post_type == 'event-recurring' ){
			$EM_Event = EM_Event::find_by_post($post);
			?>
			<script type="text/javascript">
				jQuery(document).ready( function($){
					if(!EM.recurrences_menu){
						$('#menu-posts-'+EM.event_post_type+', #menu-posts-'+EM.event_post_type+' > a').addClass('wp-has-current-submenu');
					}
				});
			</script>
			<?php
		}
	}
	
	/**
	 * Beacuse in wp admin recurrences get saved early on during save_post, meta added by  other plugins to the recurring event template don't get copied over to recurrences
	 * This re-saves meta late in save_post to correct this issue, in the future when recurrences refer to one post, this shouldn't be an issue 
	 * @param int $post_id
	 */
	public static function save_post($post_id){
		global $EM_SAVING_EVENT, $EM_EVENT_SAVE_POST;
		if( !empty($EM_SAVING_EVENT) ) return; //never proceed with this if using EM_Event::save(); which only gets executed outside wp admin
		$post_type = get_post_type($post_id);
		$saving_status = !in_array(get_post_status($post_id), array('trash','auto-draft')) && !defined('DOING_AUTOSAVE');
		if(!defined('UNTRASHING_'.$post_id) && $post_type == 'event-recurring' && $saving_status && !empty($EM_EVENT_SAVE_POST) ){
			$EM_Event = EM_Event::find_by_post_id($post_id);
			$EM_Event->post_type = $post_type;
			//get the list post IDs for recurrences this recurrence
		 	if( !$EM_Event->save_events() && ( $EM_Event->is_published() || 'future' == $EM_Event->post_status ) ){
				$EM_Event->set_status(null, true);
		 	}
		}
		$EM_EVENT_SAVE_POST = false; //last filter of save_post in EM for events
	}

	public static 	function before_delete_post($post_id){
		if(get_post_type($post_id) == 'event-recurring'){
			$EM_Event = EM_Event::find_by_post_id($post_id);
			do_action('em_event_delete_pre ',$EM_Event);
			//now delete recurrences
			//only delete other events if this isn't a draft-never-published event
			if( !empty($EM_Event->event_id) ){
    			$events_array = EM_Events::find( array('recurrence'=>$EM_Event->event_id, 'scope'=>'all', 'status'=>'everything' ) );
    			foreach($events_array as $event){
    				/* @var $event EM_Event */
    				if($EM_Event->event_id == $event->recurrence_id && !empty($event->recurrence_id) ){ //double check the event is a recurrence of this event
    					wp_delete_post($event->post_id, true);
    				}
    			}
			}
			$EM_Event->post_type = EM_POST_TYPE_EVENT; //trick it into thinking it's one event.
			$EM_Event->delete_meta();
		}
	}
	
	public static function trashed_post($post_id){
		if(get_post_type($post_id) == 'event-recurring'){
			$EM_Event = EM_Event::find_by_post_id($post_id);
			$EM_Event->set_status(null);
			//only trash other events if this isn't a draft-never-published event
			if( !empty($EM_Event->event_id) ){
    			//now trash recurrences
    			$events_array = EM_Events::find( array('recurrence_id'=>$EM_Event->event_id, 'scope'=>'all', 'status'=>'everything' ) );
    			foreach($events_array as $event){
    				/* @var $event EM_Event */
    				if($EM_Event->event_id == $event->recurrence_id ){ //double check the event is a recurrence of this event
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
			$EM_Event = EM_Event::find_by_post_id($post_id);
			//only untrash other events if this isn't a draft-never-published event, because if so it never had other events to untrash
			if( !empty($EM_Event->event_id) ){
    			$events_array = EM_Events::find( array('recurrence_id'=>$EM_Event->event_id, 'scope'=>'all', 'status'=>'everything' ) );
    			foreach($events_array as $event){
    				/* @var $event EM_Event */
    				if($EM_Event->event_id == $event->recurrence_id){
    					wp_untrash_post($event->post_id);
    				}
    			}
			}
		}
	}
	
	public static function untrashed_post($post_id){
		if(get_post_type($post_id) == 'event-recurring'){
			EM_Event::find_by_post_id($post_id)->set_status(1);
		}
	}
	
	public static function meta_boxes( $post ){
		$EM_Event = EM_Event::find_by_post($post) ?? new EM_Event();
		
	
		if( get_option('dbem_rsvp_enabled') && $EM_Event->can_manage('manage_bookings','manage_others_bookings') ){
			add_meta_box('em-event-bookings', __('Bookings/Registration','events'), array('EM_Event_Post_Admin','meta_box_bookings'),'event-recurring', 'normal','high');
		}
		
		
	}
	

}
add_action('admin_init',array('EM_Event_Recurring_Post_Admin','init'));
