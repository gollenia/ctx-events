<?php

namespace Contexis\Events;

use Contexis\Events\Collections\BookingCollection;
use Contexis\Events\Intl\Date;
use Contexis\Events\Models\Booking;

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
		$bookings = BookingCollection::find(['limit' => 5, 'orderby' => 'booking_id', 'order' => 'DESC']);
		
		?>
		<div class="bookings-block">
			<h3><?php _e('Bookings that need attention', 'events-manager'); ?></h3>
			<ul class="bookings-list">
				<?php foreach($bookings as $booking) : ?>
					<li>
						<span class="em-date"><?php echo Date::get_date($booking->date->getTimestamp()); ?></span>
						<a href="#"> <?php echo $booking->get_full_name(); ?> - <?php echo $booking->get_event()->event_name; ?> </a>
					</li>
				<?php endforeach; ?>
			</ul>
			<div>
				<a href="<?php echo admin_url('edit.php?post_type=event&page=events-bookings'); ?>">View all bookings</a>
			</div>
		</div>
		<?php
	}
}

Dashboard::init();
