<?php

namespace Contexis\Events;

class Dashboard {

	public static function init() {
		add_action('wp_dashboard_setup', [self::class, 'add_dashboard_box']);
	}

	public static function add_dashboard_box() {
		wp_add_dashboard_widget( 
			'event-bookings', 
			__('Latest Event Bookings', 'events-manager'),
			[self::class, 'bookings_box']
		);
	}

	public static function bookings_box() {
		$bookings = \EM_Bookings::get(['limit' => 5, 'orderby' => 'booking_id', 'order' => 'DESC']);
		
		?>
		<table style="width:100%">
			<?php foreach($bookings as $booking) : ?>
				<?php 
					$status = array_search($EM_Booking->booking_status, array_column($booking->get_statuus(), 'search'));
				?>
				<tr>
					<td><span class="em-label em-label-<?php echo $status ?>"><i class="material-symbols-outlined"><?php echo $booking->get_status_icon() ?></i></span></td>
					<td><a href="#"> <?php echo $booking->get_person()->get_name(); ?> - <?php echo $booking->get_event()->event_name; ?> </a></td>
					<td><span class="em-date"><?php echo date('d.m.Y', strtotime($booking->booking_date)); ?></span></td>
			</tr>
			<?php endforeach; ?>
			</table>
		<div>
			<a href="<?php echo admin_url('edit.php?post_type=event&page=events-bookings'); ?>">View all bookings</a>
		</div>
		<?php
	}
}

Dashboard::init();
