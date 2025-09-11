<?php
namespace Contexis\Events\Admin;

use Contexis\Events\Collections\BookingCollection;
use Contexis\Events\Collections\EventCollection;
use Contexis\Events\Models\Booking;
use Contexis\Events\Models\BookingStatus;
use Contexis\Events\PostTypes\EventPost;
use Contexis\Events\PostTypes\LocationPost;
use Contexis\Events\Repositories\BookingRepository;

class SidebarMenu {
	public static function init(){
		$instance = new self();
		add_action('admin_menu',[$instance, 'admin_menu']);
		add_filter( 'plugin_action_links_events/events.php', [$instance, 'plugin_action_links'], 10, 3 );	
	}

	public function count_bookings() : int {
		if( !get_option('dbem_rsvp_enabled') ) return 0;
		$count = apply_filters('em_bookings_pending_count', 0);
		
		if( get_option('dbem_bookings_approval') == 1){ 
			BookingRepository::sum_spaces(0, [BookingStatus::PENDING]);
		}
		
		return $count;
	}

	function admin_menu() : void {

		add_submenu_page(
			'edit.php?post_type=' . EventPost::POST_TYPE, 
			__('Getting Help for Events Manager','events'),
			__('Help','events'), 
			'manage_options', 
			"events-help", 
			'em_admin_help_page', 
			50
		);

		add_submenu_page(
			'edit.php?post_type=' . EventPost::POST_TYPE, 
			__('Edit booking and attendee forms','events'),
			__('Forms','events'), 
			'manage_options', 
			"events-forms", 
			["\\Contexis\\Events\\Forms\\Admin", "option_page"]
		);

		add_submenu_page(
			'edit.php?post_type=' . EventPost::POST_TYPE,
			__('Events Manager Settings','events'),
			__('Settings','events'), 
			'manage_options', 
			"events-options", 
			'em_admin_options_page',
			99
		);

		if( !get_option('dbem_rsvp_enabled') ) return;
		
		global $menu;
		$bookings_count = $this->count_bookings();
		$bookings_num = $bookings_count > 0 ? '&nbsp;<span class="update-plugins count-'.$bookings_count.'"><span class="plugin-count">'.$bookings_count.'</span></span>' : '';
		
		add_submenu_page('edit.php?post_type='.EventPost::POST_TYPE, __('Bookings', 'events'), __('Bookings', 'events') . $bookings_num, 'manage_bookings', 'events-bookings', "em_bookings_page_new", 1);
		
		if( !empty($bookings_num) ) {
			foreach ( (array)$menu as $key => $parent_menu ) {
				if ( $parent_menu[2] == 'edit.php?post_type='.EventPost::POST_TYPE ){
					$menu[$key][0] = $menu[$key][0]. '&nbsp;' . $bookings_num;
					break;
				}
			}
		}

		
	}

	public function plugin_action_links(array $actions, string $file, $plugin_data) : array {
		array_unshift($actions, sprintf( '<a href="'.EventPost::get_admin_url().'&amp;page=events-options">%s</a>', __('Settings', 'events') ));
		return $actions;
	}

}