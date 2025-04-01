<?php

use Contexis\Events\Collections\EventCollection;
use Contexis\Events\Models\Event;
use Contexis\Events\Utilities;

class EM_Event_Posts_Admin{
	public static function init(){
		global $pagenow;
		if( $pagenow == 'edit.php' && !empty($_REQUEST['post_type']) && $_REQUEST['post_type'] == EM_POST_TYPE_EVENT ){ //only needed for events list
			if( !empty($_REQUEST['category_id']) && is_numeric($_REQUEST['category_id']) ){
				$term = get_term_by('id', absint($_REQUEST['category_id']), EM_TAXONOMY_CATEGORY);
				if( !empty($term->slug) ){
					$_REQUEST['category_id'] = $term->slug;
				}
			}
			//admin warnings
            add_action('admin_notices', 'EM_Event_Posts_Admin::admin_notices');
			//hide some cols by default:
			$screen = 'edit-'.EM_POST_TYPE_EVENT;
			$hidden = get_user_option( 'manage' . $screen . 'columnshidden' );
			if( $hidden === false ){
				$hidden = array('event-id');
				update_user_option(get_current_user_id(), "manage{$screen}columnshidden", $hidden, true);
			}
			//deal with actions
			$row_action_type = is_post_type_hierarchical( EM_POST_TYPE_EVENT ) ? 'page_row_actions' : 'post_row_actions';
			add_filter($row_action_type, array('EM_Event_Posts_Admin','row_actions'),10,2);
			add_action('admin_head', array('EM_Event_Posts_Admin','admin_head'));
		}
		//collumns
		add_filter('manage_'.EM_POST_TYPE_EVENT.'_posts_columns' , array('EM_Event_Posts_Admin','columns_add'));
		add_action('manage_'.EM_POST_TYPE_EVENT.'_posts_custom_column' , array('EM_Event_Posts_Admin','columns_output'),10,2 );
		add_filter('manage_edit-'.EM_POST_TYPE_EVENT.'_sortable_columns', array('EM_Event_Posts_Admin','sortable_columns') );
		//clean up the views in the admin selection area - WIP
		//add_filter('views_edit-'.EM_POST_TYPE_EVENT, array('EM_Event_Posts_Admin','restrict_views'),10,2);
		//add_filter('views_edit-event-recurring', array('EM_Event_Posts_Admin','restrict_views'),10,2);
		//add filters to event post list tables
		add_action('restrict_manage_posts', array('EM_Event_Posts_Admin','restrict_manage_posts'));
	}
	
	public static function admin_head(){
		//quick hacks to make event admin table make more sense for events
		?>
		<script type="text/javascript">
			jQuery(document).ready( function($){
				$('.inline-edit-date').prev().css('display','none').next().css('display','none').next().css('display','none');
				$('.em-detach-link').on('click', function( event ){
					if( !confirm(EM.event_detach_warning) ){
						event.preventDefault();
						return false;
					}
				});
				$('.em-delete-recurrence-link').on('click', function( event ){
					if( !confirm(EM.delete_recurrence_warning) ){
						event.preventDefault();
						return false;
					}
				});
			});
		</script>
		<style>
			table.fixed{ table-layout:auto !important; }
			.tablenav select[name="m"] { display:none; }
		</style>
		<?php
	}
	
    public static function admin_notices(){
        if( !empty($_REQUEST['recurrence_id']) && is_numeric($_REQUEST['recurrence_id']) ){
            $event = Event::find_by_event_id( absint($_REQUEST['recurrence_id']) );
            ?>
            <div class="notice notice-info">
                <p><?php echo sprintf(esc_html__('You are viewing individual recurrences of recurring event %s.', 'events'), '<a href="'.$event->get_edit_url().'">'.$event->event_name.'</a>'); ?></p>
                <p><?php esc_html_e('You can edit individual recurrences and disassociate them with this recurring event.', 'events'); ?></p>
            </div>
            <?php
        }
    }
	

	
	/**
	 * Adds Future view to make things simpler, and also changes counts if user doesn't have edit_others_events permission
	 * @param array $views
	 * @return array
	 */
	public static function restrict_views( $views ){
		global $wp_query;
		//TODO alter views of locations, events and recurrences, specifically find a good way to alter the wp_count_posts method to force user owned posts only
		$post_type = get_current_screen()->post_type;
		if( in_array($post_type, array(EM_POST_TYPE_EVENT, 'event-recurring')) ){
			//get counts for future events
			$num_posts = wp_count_posts( $post_type, 'readable' );
			//prepare to alter cache if neccessary
			if( !isset($num_posts->em_future) ){
				$cache_key = $post_type;
				$user = wp_get_current_user();
				if ( is_user_logged_in() && !current_user_can('read_private_events') ) {
					$cache_key .= '_readable_' . $user->ID; //as seen on wp_count_posts
				}
				$args = array('scope'=>'future', 'status'=>'all');
				if( $post_type == 'event-recurring' ) $args['recurring'] = 1;
				$num_posts->em_future = EventCollection::find($args)->count();
				wp_cache_set($cache_key, $num_posts, 'counts');
			}
			$class = '';
			//highlight the 'Future' status if necessary
			if( empty($_REQUEST['post_status']) && !empty($wp_query->query_vars['scope']) && $wp_query->query_vars['scope'] == 'future'){
				$class = ' class="current"';
				foreach($views as $key => $view){
					$views[$key] = str_replace(' class="current"','', $view);
				}
			}
			//change the 'All' status to have scope=all
			$views['all'] = str_replace('edit.php?', 'edit.php?scope=all&', $views['all'] );
			//merge new custom status into views
			$old_views = $views;
			$views = array('em_future' => "<a href='edit.php?post_type=$post_type'$class>" . sprintf( _nx( 'Future <span class="count">(%s)</span>', 'Future <span class="count">(%s)</span>', $num_posts->em_future, 'events', 'events'), number_format_i18n( $num_posts->em_future ) ) . '</a>');
			$views = array_merge($views, $old_views);
		}
		
		return $views;
	}
	
	public static function restrict_manage_posts(){
		global $wp_query;
		Utilities::object_to_js_console($wp_query);
		if( $wp_query->query_vars['post_type'] == 'event' || $wp_query->query_vars['post_type'] == 'event-recurring' ){
			?>
			
			<select name="scope">
				<?php
				$scope = (!empty($wp_query->query_vars['scope'])) ? $wp_query->query_vars['scope']:'future';
				foreach ( EM_Object::get_scopes() as $key => $value ) {
					$selected = "";
					if ($key == $scope)
						$selected = "selected='selected'";
					echo "<option value='$key' $selected>$value</option>  ";
				}
				?>
			</select>
			<?php
			
			//Categories
			$selected = !empty($_GET['event-categories']) ? $_GET['event-categories'] : 0;
			wp_dropdown_categories(array( 'hide_empty' => 1, 'name' => EM_TAXONOMY_CATEGORY,
							'hierarchical' => true, 'orderby'=>'name', 'id' => EM_TAXONOMY_CATEGORY,
							'taxonomy' => EM_TAXONOMY_CATEGORY, 'selected' => $selected,
							'show_option_all' => __('View all categories', 'events')));
		
            if( !empty($_REQUEST['author']) ){
            	?>
            	<input type="hidden" name="author" value="<?php echo esc_attr($_REQUEST['author']); ?>" />
            	<?php            	
            }
			
		}
	}
	
	public static function views($views){
		if( !current_user_can('edit_others_events') ){
			//alter the views to reflect correct numbering
			 
		}
		return $views;
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
	    	'bookingdate' => __('Booking Date','events'),
	    	'extra' => '',
			'spaces' => __('Available','events'),
			'booked' => __('Booked','events'),
	    ));
	    if( !get_option('dbem_locations_enabled') ){
	    	unset($columns['location']);
	    }
	    return $columns;
	}
	
	public static function columns_output( $column ) {
		$event = Event::find_by_post_id(get_the_ID());
		
		/* @var $post Event */
		switch ( $column ) {
			case 'event-id':
				echo $event->event_id;
				break;
			case 'location':
				//get meta value to see if post has location, otherwise
				$location = $event->get_location();
				if( $event->has_location() ){
					$actions = array();
					$actions[] = "<a href='". esc_url($location->get_permalink())."'>". esc_html__('View') ."</a>";
					if( $location->can_manage('edit_locations', 'edit_others_locations') ) {
						$actions[] = "<a href='". esc_url($location->get_edit_url())."'>". esc_html__('Edit') ."</a>";
					}
					echo "<strong><a href='". $location->get_permalink()."'>" . $location->location_name . "</a></strong>";
					echo "<span class='row-actions'> - ". implode(' | ', $actions) . "</span>";
					echo "<br/>" . $location->location_address . " - " . $location->location_town;
				}else{
					echo __('None','events');
				}
				break;
			case 'date-time':
				
				echo \Contexis\Events\Intl\Date::get_date($event->start()->getTimestamp(), $event->end()->getTimestamp());;
				echo "<br />";
				if(!$event->event_all_day){
					echo \Contexis\Events\Intl\Date::get_time($event->start()->getTimestamp(), $event->end()->getTimestamp());
				}else{
					echo __('All Day','events');
				}
				break;
			case 'extra':
				if ( $event->is_recurrence() && current_user_can('edit_recurring_events','edit_others_recurring_events') ) {
					$actions = array();
					if( $event->get_event_recurrence()->can_manage('edit_recurring_events', 'edit_others_recurring_events') ){
						$actions[] = '<a href="'. admin_url() .'post.php?action=edit&amp;post='. $event->get_event_recurrence()->post_id .'">'. esc_html__( 'Edit Recurring Events', 'events'). '</a>';
						$actions[] = '<a class="em-detach-link" href="'. esc_url($event->get_detach_url()) .'">'. esc_html__('Detach', 'events') .'</a>';
					}
					if( $event->get_event_recurrence()->can_manage('delete_recurring_events', 'delete_others_recurring_events') ){
						$actions[] = '<span class="trash"><a class="em-delete-recurrence-link" href="'. get_delete_post_link($event->get_event_recurrence()->post_id) .'">'. esc_html__('Delete','events') .'</a></span>';
					}
					?>
					<strong>
					<?php echo $event->get_recurrence_description(); ?>
					</strong>
					<?php if( !empty($actions) ): ?>
					<br >
					<div class="row-actions">
						<?php echo implode(' | ', $actions); ?>
					</div>
					<?php endif;
				}
				break;
			
			case 'bookingdate':
				$start = $event->get_rsvp_start();
				$end = $event->get_rsvp_end();
				if(!$start || !$end){
					echo __('No booking date set','events');
					break;
				}
				if( $start->getTimestamp() > time() ){
					$description = __("Booking starts", "events");
					$date = \Contexis\Events\Intl\Date::get_date($start->getTimestamp());
					$color = '#b32d2e';
				}elseif( $end->getTimestamp() < time() ){
					$description = __("Booking ended", "events");
					$date = \Contexis\Events\Intl\Date::get_date($end->getTimestamp());
					$color = '#b32d2e';
				}else{
					$description = __("Booking open until", "events");
					$date = \Contexis\Events\Intl\Date::get_date($end->getTimestamp());
					$color = '#0073aa';
				}
				echo "<div style=\"color: $color\"><strong>$description</strong><br /><time>$date</time></div>";
				break;

			case 'spaces':
				if( get_option('dbem_rsvp_enabled') == 1 && !empty($event->event_rsvp) && $event->can_manage('manage_bookings','manage_others_bookings')){
					?>
					
					<b><?php echo $event->get_bookings()->get_available_spaces(); echo " "; echo __("Free", "events") ?> </b><br> <?php echo __("Off", "events"); echo " "; echo $event->get_bookings()->get_spaces(); ?>
					
				
					<?php
					
				}
				break;
			case 'booked':
				if( get_option('dbem_rsvp_enabled') == 1 && !empty($event->event_rsvp) && $event->get_bookings()->get_spaces()){
					$booked_percent = $event->get_bookings()->get_booked_spaces() / ($event->get_bookings()->get_spaces() / 100);
					$pending_percent = $event->get_bookings()->get_pending_spaces() / ($event->get_bookings()->get_spaces() / 100);
					?>
					
					<b style="white-space: nowrap;"><?php echo $event->get_bookings()->get_booked_spaces(); echo " ";  ?> /
					<?php echo $event->get_bookings()->get_pending_spaces(); echo " "; echo __("Pending", "events") ?></b>
					<div class="em-booking-graph">
									<?php if($booked_percent < 100) { ?>
										<div class="em-booking-graph-booked <?php if($pending_percent) echo "cut" ?>" style="width:<?php echo $booked_percent ?>%;"></div>
										<div class="em-booking-graph-pending <?php if($booked_percent) echo "cut" ?>" style="width:<?php echo $pending_percent ?>%;"></div>
									<?php } ?>
									<?php if($booked_percent >= 100) { ?>
										<div class="em-booking-graph-full" style="width:100%;"></div>
									<?php } ?>
								</div>
					<?php

				}
				break;
			case 'actions':
				echo '<a href="' . $event->get_bookings_url() . '">'. __("Bookings",'events') . '</a>'; 
		}
	}
	
	public static function row_actions($actions, $post){
		if($post->post_type == EM_POST_TYPE_EVENT){
			$event = Event::find_by_post($post);
			unset($actions['inline hide-if-no-js']);
			unset($actions['edit']);
			$actions['duplicate'] = '<a href="'.$event->duplicate_url().'" title="'.sprintf(__('Duplicate %s','events'), __('Event','events')).'">'.__('Duplicate','events').'</a>';
			$actions['bookings'] = '<a href="'.$event->get_bookings_url().'" title="'.__('View Bookings','events').'">'.__('Bookings','events').'</a>';
		}
		return $actions;
	}
	
	public static function sortable_columns( $columns ){
		$columns['date-time'] = 'date-time';
		return $columns;
	}
	
}
add_action('admin_init', array('EM_Event_Posts_Admin','init'));

/*
 * Recurring Events
 */
class EM_Event_Recurring_Posts_Admin{
	public static function init(){
		global $pagenow;
		if( $pagenow == 'edit.php' && !empty($_REQUEST['post_type']) && $_REQUEST['post_type'] == 'event-recurring' ){
			//hide some cols by default:
			$screen = 'edit-event-recurring';
			$hidden = get_user_option( 'manage' . $screen . 'columnshidden' );
			if( $hidden === false ){
				$hidden = array('event-id');
				update_user_option(get_current_user_id(), "manage{$screen}columnshidden", $hidden, true);
			}
			//notices			
			add_action('admin_notices',array('EM_Event_Recurring_Posts_Admin','admin_notices'));
			add_action('admin_head', array('EM_Event_Recurring_Posts_Admin','admin_head'));
			//actions
			$row_action_type = is_post_type_hierarchical( EM_POST_TYPE_EVENT ) ? 'page_row_actions' : 'post_row_actions';
			add_filter($row_action_type, array('EM_Event_Recurring_Posts_Admin','row_actions'),10,2);
		}
		//collumns
		add_filter('manage_event-recurring_posts_columns' , array('EM_Event_Recurring_Posts_Admin','columns_add'));
		add_filter('manage_event-recurring_posts_custom_column' , array('EM_Event_Recurring_Posts_Admin','columns_output'),10,1 );
		add_action('restrict_manage_posts', array('EM_Event_Posts_Admin','restrict_manage_posts'));
		add_filter( 'manage_edit-event-recurring_sortable_columns', array('EM_Event_Posts_Admin','sortable_columns') );
	}
	
	public static function admin_notices(){
		?>
		<div class="notice notice-info">
			<p><?php esc_html_e( 'Modifications to recurring events will be applied to all recurrences and will overwrite any changes made to those individual event recurrences.', 'events'); ?></p>
			<p><?php esc_html_e( 'Bookings to individual event recurrences will be preserved if event times and ticket settings are not modified.', 'events'); ?></p>
			<p>
				<a href="<?php echo esc_url( admin_url('edit.php?post_type=event') ); ?>">
					<strong><?php esc_html_e('You can edit individual recurrences and disassociate them with a recurring event to prevent getting overwritten.', 'events'); ?></strong>
				</a>
	    	</p>
		</div>
		<?php
	}
	
	public static function admin_head(){
		//quick hacks to make event admin table make more sense for events
		?>
		<script type="text/javascript">
			jQuery(document).ready( function($){
				$('.inline-edit-date').prev().css('display','none').next().css('display','none').next().css('display','none');
				if(!EM.recurrences_menu){
					$('#menu-posts-'+EM.event_post_type+', #menu-posts-'+EM.event_post_type+' > a').addClass('wp-has-current-submenu');
				}
			});
		</script>
		<style>
			table.fixed{ table-layout:auto !important; }
			.tablenav select[name="m"] { display:none; }
		</style>
		<?php
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
		if( !get_option('dbem_locations_enabled') ){
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
}
add_action('admin_init', array('EM_Event_Recurring_Posts_Admin','init'));