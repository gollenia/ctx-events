<?php

use Contexis\Events\Collections\EventCollection;
use Contexis\Events\Views\EventView;

/**
 * Determines whether to show event page or events page, and saves any updates to the event or events
 * @return null
 */
function em_bookings_events_table() {
	//TODO Simplify panel for events, use form flags to detect certain actions (e.g. submitted, etc)


	$scope_names = array (
		'past' => __ ( 'Past events', 'events'),
		'all' => __ ( 'All events', 'events'),
		'future' => __ ( 'Future events', 'events')
	);
	
	$action_scope = ( !empty($_REQUEST['em_obj']) && $_REQUEST['em_obj'] == 'em_bookings_events_table' );
	$action = ( $action_scope && !empty($_GET ['action']) ) ? $_GET ['action']:'';
	$order = ( $action_scope && !empty($_GET ['order']) ) ? $_GET ['order']:'ASC';
	$limit = ( $action_scope && !empty($_GET['limit']) ) ? $_GET['limit'] : 20;//Default limit
	$page = ( $action_scope && !empty($_GET['pno']) ) ? $_GET['pno']:1;
	$offset = ( $action_scope && $page > 1 ) ? ($page-1)*$limit : 0;
	$scope = ( $action_scope && !empty($_GET ['scope']) && array_key_exists($_GET ['scope'], $scope_names) ) ? $_GET ['scope']:'future';
	
	// No action, only showing the events list
	switch ($scope) {
		case "past" :
			$title = __ ( 'Past Events', 'events');
			break;
		case "all" :
			$title = __ ( 'All Events', 'events');
			break;
		default :
			$title = __ ( 'Future Events', 'events'); 
			$scope = "future";
	}
	
	$events = EventCollection::find( [
		'scope'=>$scope, 
		'limit'=>$limit, 
		'offset' => $offset, 
		'order'=>$order, 
		'orderby'=>'event_start', 
		'bookings'=> 1, 
		'pagination' => 1 
		] );

	$events_count = $events->count();

	?>
	<div class="wrap em_bookings_events_table em_obj">
		
		<form id="posts-filter" action="" method="get">
			<input type="hidden" name="em_obj" value="em_bookings_events_table" />
			<?php if(!empty($_GET['page'])): ?>
			<input type='hidden' name='page' value='events-bookings' />
			<?php endif; ?>		
			<div class="tablenav">			
				<div class="alignleft actions">
					<!--
					<select name="action">
						<option value="-1" selected="selected"><?php _e( 'Bulk Actions' ); ?></option>
						<option value="deleteEvents"><?php _e( 'Delete selected','events'); ?></option>
					</select> 
					<input type="submit" value="<?php _e( 'Apply' ); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
					 --> 
					<select name="scope">
						<?php
						foreach ( $scope_names as $key => $value ) {
							$selected = "";
							if ($key == $scope)
								$selected = "selected='selected'";
							echo "<option value='$key' $selected>$value</option>  ";
						}
						?>
					</select>
					<button id="post-query-submit" class="button-secondary" type="" value="" ><?php _e( 'Filter', 'events' )?></button>
				</div>
				<?php 
				if ( $events_count >= $limit ) {
					$events_nav = Contexis\Events\Admin\Pagination::paginate( $events_count, $limit, $page, array('em_ajax'=>0, 'em_obj'=>'em_bookings_events_table'));
					echo $events_nav;
				}
				?>
			</div>
			<div class="clear"></div>
			<?php
			if (empty ( $events )) {
				_e ( 'no events','events');
			} else {
			?>
			<div class='table-wrap'>	
			<table class="widefat">
				<thead>
					<tr>
						<th><?php _e( 'Event', 'events'); ?></th>
						<th><?php _e( 'Date', 'events'); ?></th>
						<th><?php _e( 'Booked', 'events'); ?></th>
						<th><?php _e( 'Available', 'events'); ?></th>
						
					</tr>
				</thead>
				<tbody>
					<?php 
					$rowno = 0;
					foreach ( $events as $event ) {
						$rowno++;
						$class = ($rowno % 2) ? ' class="alternate"' : '';
						$style = "";
						$booked_percent = 0;
						$pending_percent = 0;
						
						if($event->spaces->available() > 0) {
							$booked_percent = $event->spaces->booked() / ($event->spaces->capacity() / 100);
							$pending_percent = $event->spaces->pending() / ($event->spaces->capacity() / 100);
						}
						
						
						if ($event->start()->getTimestamp() < time() && $event->end()->getTimestamp() < time()){
							$style = "style ='background-color: #FADDB7;'";
						}							
						?>
						<tr <?php echo "$class $style"; ?>>
							<td>
								<strong>
									<?php echo EventView::render($event, '#_BOOKINGSLINK'); ?>
									</strong>
									<div class="row-actions "><span class="trash"><a href="https://kids-team.internal/wp-admin/post.php?post=27&amp;action=trash&amp;_wpnonce=8ebb708296" class="submitdelete" aria-label="„Teenagerfreizeit“ in den Papierkorb verschieben">Buchungen löschen</a> | </span><span class="trash"><a href="https://kids-team.internal/events/teenagerfreizeit/" rel="bookmark" aria-label="„Teenagerfreizeit“ ansehen">Absagen</a></span></div>
								
							</td>
							<td>
								<?php echo EventView::render($event, "#_EVENTDATES" )?>
							</td>
							<td>
								
							<b><?php echo $event->spaces->available(); echo " "; echo __("Free", "events") ?> </b><br> <?php echo __("Off", "events"); echo " "; echo $event->spaces->capacity(); ?>
					
							</td>
							<td >
								<b><?php echo $event->spaces->booked(); echo " ";  ?> /
								<?php echo $event->spaces->pending(); echo " "; echo __("Pending", "events") ?></b>
								<div class="em-booking-graph">
									<?php if($booked_percent < 100) { ?>
										<div class="em-booking-graph-booked <?php if($pending_percent) echo "cut" ?>" style="width:<?php echo $booked_percent ?>%;"></div>
										<div class="em-booking-graph-pending <?php if($booked_percent) echo "cut" ?>" style="width:<?php echo $pending_percent ?>%;"></div>
									<?php } ?>
									<?php if($booked_percent >= 100) { ?>
										<div class="em-booking-graph-full" style="width:100%;"></div>
									<?php } ?>
								</div>
							</td>
							
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			</div>
			<?php
			} // end of table
			?>
			<div class='tablenav'>
				<div class="alignleft actions">
				<br class='clear' />
				</div>
				<?php if (!empty($events_nav) &&  $events_count >= $limit ) : ?>
				<div class="tablenav-pages">
					<?php
					echo $events_nav;
					?>
				</div>
				<?php endif; ?>
				<br class='clear' />
			</div>
		</form>
	</div>
	<?php
}

?>