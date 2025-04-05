<?php

use Contexis\Events\Collections\BookingCollection;
use Contexis\Events\Export\BookingsTable;
use Contexis\Events\Model\Booking;
use Contexis\Events\Models\Event;
use Contexis\Events\Models\Ticket;
use Contexis\Events\PostTypes\EventPost;
use Contexis\Events\Views\EventView;

/**
 * Decide what content to show in the bookings section. 
 */
function em_bookings_page(){
	//First any actions take priority
	do_action('em_bookings_admin_page');
	if( !empty($_REQUEST['_wpnonce']) ){ $_REQUEST['_wpnonce'] = $_GET['_wpnonce'] = $_POST['_wpnonce'] = esc_attr($_REQUEST['_wpnonce']); } //XSS fix just in case here too
	if( !empty($_REQUEST['action']) && substr($_REQUEST['action'],0,7) != 'booking' ){ //actions not starting with booking_
		do_action('em_bookings_'.$_REQUEST['action']);
	}elseif( !empty($_REQUEST['booking_id']) ){
		em_bookings_single();
	}elseif( !empty($_REQUEST['user_email']) ){
		em_bookings_person();
	}elseif( !empty($_REQUEST['ticket_id']) ){
		em_bookings_ticket();
	}elseif( !empty($_REQUEST['event_id']) ){
		em_bookings_event();
	}else{
		em_bookings_dashboard();
	}
}

/**
 * Generates the bookings dashboard, showing information on all events 
 */
function em_bookings_dashboard(){
	global $EM_Notices;
	?>
	<div class='wrap em-bookings-dashboard'>
		<?php if( is_admin() ): ?>
  		<h1><?php esc_html_e('Event Bookings Dashboard', 'events'); ?></h1>
  		<?php else: echo $EM_Notices; ?>
  		<?php endif; ?>
  		<div class="em-bookings-recent">
			<h2><?php esc_html_e('Recent Bookings','events'); ?></h2>	
	  		<?php
			$bookings_table = new BookingsTable();
			$bookings_table->output();
	  		?>
  		</div>
  		<br class="clear" />
  		<div class="em-bookings-events">
			<h2><?php esc_html_e('Events With Bookings Enabled','events'); ?></h2>		
			<?php em_bookings_events_table(); ?>
			<?php do_action('em_bookings_dashboard'); ?>
		</div>
	</div>
	<?php		
}

/**
 * Shows all booking data for a single event 
 */
function em_bookings_event(){
	global $EM_Notices;
	$event = \Contexis\Events\Models\Event::find_by_event_id($_REQUEST['event_id']);
	
	//check that user can access this page
	if( is_object($event) && !$event->can_manage('manage_bookings','manage_others_bookings') ){
		?>
		<div class="wrap"><h2><?php esc_html_e('Unauthorized Access','events'); ?></h2><p><?php esc_html_e('You do not have the rights to manage this event.','events'); ?></p></div>
		<?php
		return false;
	}
	$header_button_classes = is_admin() ? 'page-title-action':'button add-new-h2';
	?>
	<div class='wrap'>
		<?php if( is_admin() ): ?><h1 class="wp-heading-inline"><?php else: ?><h2><?php endif; ?>		
  			<?php echo sprintf(__('Manage %s Bookings', 'events'), "'{$event->event_name}'"); ?>
  		<?php if( is_admin() ): ?></h1><?php endif; ?>
  			<a href="<?php echo $event->get_permalink(); ?>" class="<?php echo $header_button_classes; ?>"><?php echo sprintf(__('View %s','events'), __('Event', 'events')) ?></a>
  			<a href="<?php echo $event->get_edit_url(); ?>" class="<?php echo $header_button_classes; ?>"><?php echo sprintf(__('Edit %s','events'), __('Event', 'events')) ?></a>
  			<?php if( locate_template('plugins/events/templates/csv-event-bookings.php', false) ): //support for legacy template ?>
  			<a href='<?php echo EventPost::get_admin_url() ."&amp;page=events-bookings&amp;action=bookings_export_csv&amp;_wpnonce=".wp_create_nonce('bookings_export_csv')."&amp;event_id=".$event->event_id ?>' class="<?php echo $header_button_classes; ?>"><?php esc_html_e('Export CSV','events')?></a>
  			<?php endif; ?>
  			<?php do_action('em_admin_event_booking_options_buttons'); ?>
		<?php if( !is_admin() ): ?></h2><?php else: ?><hr class="wp-header-end" /><?php endif; ?>
  		<?php if( !is_admin() ) echo $EM_Notices; ?>  
		<div>
			<p><strong><?php esc_html_e('Event Name','events'); ?></strong> : <?php echo esc_html($event->event_name); ?></p>
			<p>
				<strong><?php esc_html_e('Availability','events'); ?></strong> : 
				<?php echo $event->get_bookings()->get_booked_spaces() . '/'. $event->get_bookings()->get_spaces() ." ". __('Spaces confirmed','events'); ?>
				<?php if( get_option('dbem_bookings_approval_reserved') ): ?>
				, <?php echo $event->get_bookings()->get_available_spaces() . '/'. $event->get_bookings()->get_spaces() ." ". __('Available spaces','events'); ?>
				<?php endif; ?>
			</p>
			<p>
				<strong><?php esc_html_e('Date','events'); ?></strong> : 
				<?php echo EventView::render($event, "EVENT_DATES") ?>						
			</p>
			<p>
				<strong><?php esc_html_e('Location','events'); ?></strong> :
				<?php if( $event->location_id == 0 ): ?>
				<em><?php esc_html_e('No Location', 'events'); ?></em>
				<?php else: ?>
				<a class="row-title" href="<?php echo admin_url(); ?>post.php?action=edit&amp;post=<?php echo $event->get_location()->post_id ?>"><?php echo ($event->get_location()->location_name); ?></a>
				<?php endif; ?>
			</p>
		</div>
		<h2><?php esc_html_e('Bookings','events'); ?></h2>
		<?php
		$bookings_table = new BookingsTable();
		$bookings_table->status = 'all';
		$bookings_table->output();
  		?>
		<?php do_action('em_bookings_event_footer', $event); ?>
	</div>
	<?php
}

/**
 * Shows a ticket view
 */
function em_bookings_ticket(){

	if (!empty($_REQUEST['ticket_id'])) {
		$ticket = new Ticket($_REQUEST['ticket_id']);
	} else {
		$ticket = new Ticket();
	}
	global $EM_Notices;
	$event = $ticket->get_event();
	//check that user can access this page
	if( is_object($ticket) && !$ticket->can_manage() ){
		?>
		<div class="wrap"><h2><?php esc_html_e('Unauthorized Access','events'); ?></h2><p><?php esc_html_e('You do not have the rights to manage this ticket.','events'); ?></p></div>
		<?php
		return false;
	}
	$header_button_classes = is_admin() ? 'page-title-action':'button add-new-h2';
	?>
	<div class='wrap'>
		<?php if( is_admin() ): ?><h1 class="wp-heading-inline"><?php else: ?><h2><?php endif; ?>
  			<?php echo sprintf(__('Ticket for %s', 'events'), "'{$event->event_name}'"); ?>
  		<?php if( is_admin() ): ?></h1><?php endif; ?>
  			<a href="<?php echo $event->get_edit_url(); ?>" class="<?php echo $header_button_classes; ?>"><?php esc_html_e('View/Edit Event','events') ?></a>
  			<a href="<?php echo $event->get_bookings_url(); ?>" class="<?php echo $header_button_classes; ?>"><?php esc_html_e('View Event Bookings','events') ?></a>
  		
		<?php if( !is_admin() ): ?></h2><?php else: ?><hr class="wp-header-end" /><?php endif; ?>
  		<?php if( !is_admin() ) echo $EM_Notices; ?>
		<div>
			<table>
				<tr><td><?php echo __('Name','events'); ?></td><td></td><td><?php echo $ticket->ticket_name; ?></td></tr>
				<tr><td><?php echo __('Description','events'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td></td><td><?php echo ($ticket->ticket_description) ? $ticket->ticket_description : '-'; ?></td></tr>
				<tr><td><?php echo __('Price','events'); ?></td><td></td><td><?php echo ($ticket->ticket_price) ? $ticket->ticket_price : '-'; ?></td></tr>
				<tr><td><?php echo __('Spaces','events'); ?></td><td></td><td><?php echo ($ticket->ticket_spaces) ? $ticket->ticket_spaces : '-'; ?></td></tr>
				<tr><td><?php echo __('Min','events'); ?></td><td></td><td><?php echo ($ticket->ticket_min) ? $ticket->ticket_min : '-'; ?></td></tr>
				<tr><td><?php echo __('Max','events'); ?></td><td></td><td><?php echo ($ticket->ticket_max) ? $ticket->ticket_max : '-'; ?></td></tr>
				<tr><td><?php echo __('Start','events'); ?></td><td></td><td><?php echo ($ticket->ticket_start) ? $ticket->start()->formatDefault() : '-'; ?></td></tr>
				<tr><td><?php echo __('End','events'); ?></td><td></td><td><?php echo ($ticket->ticket_end) ? $ticket->end()->formatDefault() : '-'; ?></td></tr>
				<?php do_action('em_booking_admin_ticket_row', $ticket); ?>
			</table>
		</div>
		<h2><?php esc_html_e('Bookings','events'); ?></h2>
		<?php
		$bookings_table = new BookingsTable();
		//$bookings_table->status = get_option('dbem_bookings_approval') ? 'needs-attention':'confirmed';
		$bookings_table->output();
  		?>
		<?php do_action('em_bookings_ticket_footer', $ticket); ?>
	</div>
	<?php	
}



/**
 * Shows all bookings made by one person.
 */
function em_bookings_person(){	
	global $EM_Notices;
	
	$has_booking = false;
	foreach(BookingCollection::find(array('booking_mail' => $_REQUEST['booking_mail'])) as $booking){
		if($booking->can_manage('manage_bookings','manage_others_bookings')){
			$has_booking = true;
		}
	}
	if( !$has_booking && !current_user_can('manage_others_bookings') ){
		?>
		<div class="wrap"><h2><?php esc_html_e('Unauthorized Access','events'); ?></h2><p><?php esc_html_e('You do not have the rights to manage this event.','events'); ?></p></div>
		<?php
		return false;
	}
	$header_button_classes = is_admin() ? 'page-title-action':'button add-new-h2';
	?>
	<div class='wrap'>
		<?php if( is_admin() ): ?><h1 class="wp-heading-inline"><?php else: ?><h2><?php endif; ?>
  			<?php esc_html_e('Manage Person\'s Booking', 'events'); ?>
  		<?php if( is_admin() ): ?></h1><?php endif; ?>
  			
  			
		<?php if( !is_admin() ): ?></h2><?php else: ?><hr class="wp-header-end" /><?php endif; ?>
  		<?php if( !is_admin() ) echo $EM_Notices; ?>
		<?php do_action('em_bookings_person_header'); ?>
  		<div id="poststuff" class="metabox-holder has-right-sidebar">
	  		<div id="post-body">
				<div id="post-body-content">
					<div id="event_name" class="stuffbox">
						<h3>
							<?php esc_html_e( 'Personal Details', 'events'); ?>
						</h3>
						<div class="">
							<h1><?php echo $booking->full_name; ?></h1>
						</div>
					</div> 
				</div>
			</div>
		</div>
		<br style="clear:both;" />
		<?php do_action('em_bookings_person_body_1'); ?>
		<h2><?php esc_html_e('Past And Present Bookings','events'); ?></h2>
		<?php
		$bookings_table = new BookingsTable();
		//$bookings_table->status = 'all';
		$bookings_table->scope = 'all';
		$bookings_table->output();
  		?>
		<?php do_action('em_bookings_person_footer'); ?>
	</div>
	<?php
}

function em_bookings_single() {
	$booking = Booking::get_by_id($_REQUEST['booking_id']);
	
	?>
	<div class='wrap' id="em-bookings-admin-booking">
		<h1 class="wp-heading-inline">
  			<?php __('Edit Booking', 'events-manager'); ?>
		</h1>
  		<div class="metabox-holder">
	  		<div class="postbox-container" style="width:99.5%">
						<?php
						
						$event = $booking->get_event();
						?>
						<div id="booking-admin" data-id="<?php echo $booking->booking_id ?>"></div>
						<?php do_action('em_bookings_admin_booking_event', $event); ?>
				
				
				<?php do_action('em_bookings_single_metabox_footer', $booking); ?>
			</div>
		</div>
		<br style="clear:both;" />
		<?php do_action('em_bookings_single_footer', $booking); ?>
	</div>
	<?php
}

?>