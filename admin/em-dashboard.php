<?php

namespace Contexis\Events;

use Contexis\Events\Collections\BookingCollection;
use Contexis\Events\Intl\Date;

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
		$bookings = BookingCollection::get(['limit' => 5, 'orderby' => 'booking_id', 'order' => 'DESC']);
		
		?>
		<table style="width:100%">
			<?php foreach($bookings as $booking) : ?>
				
				<?php 
					$status = array_search($booking->booking_status, array_column($booking->get_available_states(), 'search'));
				?>
				<tr>
					<td><span class="em-label em-label-<?php echo $status ?>"><i class="material-symbols-outlined"><?php echo $booking->get_status_icon() ?></i></span></td>
					<td><a href="#"> <?php echo $booking->get_full_name; ?> - <?php echo $booking->get_event()->event_name; ?> </a></td>
					<td><span class="em-date"><?php echo $booking->get_booking_date(); ?></span></td>
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
