<?php
/*
Plugin Name: Events
Plugin URI: https://github.com/gollenia/events
Description: Event registration and booking management for WordPress. Recurring events, locations, ical, booking registration and more!
Version: 6.9.0
Requires at least: 6.7
Requires PHP: 8.0
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Author: Thomas Gollenia
Author URI: https://github.com/gollenia/events
Text Domain: events
Domain Path: /languages
*/

use Contexis\Events\Model\Booking;
use Contexis\Events\Models\Location;
use Contexis\Events\PostTypes\EventPost;
use Contexis\Events\PostTypes\LocationPost;
class Events {
	const DIR = __DIR__;
}

function em_load_textdomain() {
	load_plugin_textdomain('events', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

add_action( 'plugin_loaded', 'em_load_textdomain', 10 );


require_once __DIR__ . '/classes/Install.php';

if(!class_exists('IntlDateFormatter')) {
	add_action( 'admin_init', ['\\Contexis\\Events\\Install', 'deactivate_plugin'] );
	add_action( 'admin_notices', ['\\Contexis\\Events\\Install', 'intallation_error_notice'] ); 
	return;
}

require_once( plugin_dir_path( __FILE__ ) . '/vendor/autoload.php');

// INCLUDES
//Base classes
require_once __DIR__ . '/polyfill.php';
require_once __DIR__ . '/classes/Utilities.php';
require_once __DIR__ . '/classes/Utilities/SQLHelper.php';
require_once __DIR__ . '/classes/Utilities/EventScope.php';
require_once __DIR__ . '/Assets.php';
require_once __DIR__ . '/classes/Options.php';
require_once __DIR__ . '/classes/Object.php';
require_once __DIR__ . '/classes/Interfaces/PostType.php';
//require_once __DIR__ . '/classes/Taxonomies/TaxonomyTerm.php';
//require_once __DIR__ . '/classes/Taxonomies/TaxonomyTerms.php';

require_once __DIR__ . '/classes/Forms/FormPost.php';

require_once __DIR__ . '/em-actions.php';
require_once __DIR__ . '/em-ical.php';

require_once __DIR__ . '/classes/Models/Booking.php';
require_once __DIR__ . '/classes/PostTypes/SpeakerPost.php';
require_once __DIR__ . '/classes/Collections/BookingCollection.php';
require_once __DIR__ . '/classes/Bookings/BookingsTable.php';
require_once __DIR__ . '/classes/Bookings/BookingsRest.php';
require_once __DIR__ . '/classes/Bookings/BookingExport.php';

require_once __DIR__ . '/classes/Models/Event.php';
require_once __DIR__ . '/classes/Events/EventRestController.php';
require_once __DIR__ . '/classes/PostTypes/EventPost.php';
require_once __DIR__ . '/classes/PostTypes/RecurringEventPost.php';
require_once __DIR__ . '/classes/Collections/EventCollection.php';
require_once __DIR__ . '/classes/Views/EventView.php';
require_once __DIR__ . '/classes/Models/Location.php';

require_once __DIR__ . '/classes/PostTypes/LocationPost.php';

require_once __DIR__ . '/classes/Views/LocationView.php';
require_once __DIR__ . '/classes/Emails/Mailer.php';
require_once __DIR__ . '/classes/Notices.php';
require_once __DIR__ . '/classes/Permalinks.php';
require_once __DIR__ . '/classes/Admin/SpeakerAdmin.php';

require_once __DIR__ . '/classes/Tickets/TicketBooking.php';
require_once __DIR__ . '/classes/Models/Ticket.php';
require_once __DIR__ . '/classes/Tickets/TicketsBookings.php';
require_once __DIR__ . '/classes/Collections/TicketCollection.php';
require_once __DIR__ . '/classes/Tickets/TicketsController.php';
//Admin Files
if( is_admin() ){
	require_once __DIR__ . '/classes/Forms/FormPostAdmin.php';
	require_once __DIR__ . '/admin/em-admin.php';
	require_once __DIR__ . '/admin/em-bookings.php';
	require_once __DIR__ . '/admin/em-docs.php';
	require_once __DIR__ . '/admin/em-help.php';
	require_once __DIR__ . '/admin/em-options.php';
	require_once __DIR__ . '/admin/em-dashboard.php';
	require_once __DIR__ . '/classes/Admin/EventAdmin.php';
	require_once __DIR__ . '/classes/Admin/RecurringEventAdmin.php';
	require_once __DIR__ . '/classes/Admin/LocationAdmin.php';
	require_once __DIR__ . '/admin/bookings/em-events.php';
}

require_once __DIR__ . '/classes/Models/Speaker.php';
require_once __DIR__ . '/classes/Export/Export.php';

require_once __DIR__ . '/classes/Forms/Forms.php';
require_once __DIR__ . '/classes/Gateways/Gateways.php';
require_once __DIR__ . '/classes/Forms/BookingsForm.php';

require_once __DIR__ . '/classes/Coupons/Coupons.php';
require_once __DIR__ . '/classes/Emails/Emails.php';
require_once __DIR__ . '/classes/Forms/UserFields.php';

global $wpdb;
$prefix = $wpdb->prefix;

define('EM_TICKETS_TABLE', $prefix.'em_tickets'); //TABLE NAME
define('EM_TICKETS_BOOKINGS_TABLE', $prefix.'em_tickets_bookings'); //TABLE NAME
define('EM_META_TABLE',$prefix.'em_meta'); //TABLE NAME
define('EM_RECURRENCE_TABLE',$prefix.'dbem_recurrence'); //TABLE NAME
define('EM_BOOKINGS_TABLE',$prefix.'em_bookings'); //TABLE NAME
define('EM_TRANSACTIONS_TABLE', $wpdb->prefix.'em_transactions'); //TABLE NAME
define('EM_EMAIL_QUEUE_TABLE', $wpdb->prefix.'em_email_queue'); //TABLE NAME
define('EM_COUPONS_TABLE', $wpdb->prefix.'em_coupons'); //TABLE NAME
define('EM_BOOKINGS_RELATIONSHIPS_TABLE', $wpdb->prefix.'em_bookings_relationships'); //TABLE NAME



/**
 * Perform init actions
 */
function em_init(){
	//Hard Links
	global $EM_Mailer, $wp_rewrite;
	
	if( $wp_rewrite->using_permalinks() ){
		define('EM_URI', trailingslashit(home_url()). EventPost::get_slug().'/'); //PAGE URI OF EM
	}else{
		define('EM_URI', trailingslashit(home_url()).'?post_type='.EventPost::POST_TYPE); //PAGE URI OF EM
	}
	
	if( $wp_rewrite->using_permalinks() ){
		$rss_url = trailingslashit(home_url()). EventPost::get_slug().'/feed/';
		define('EM_RSS_URI', $rss_url); //RSS PAGE URI via CPT archives page
	}else{
		$rss_url = add_query_arg(['post_type'=>EventPost::POST_TYPE, 'feed'=>'rss2'], home_url());
		define('EM_RSS_URI', $rss_url); //RSS PAGE URI
	}
	$EM_Mailer = new \EM_Mailer();
	//Upgrade/Install Routine
	if( !is_admin() || !current_user_can('manage_options') ) return;

	if (version_compare(\Contexis\Events\Utilities::get_installed_version(), \Contexis\Events\Utilities::get_plugin_version(), '<')) {
		require_once( dirname(__FILE__).'/em-install.php');
		em_install();
	}
}
add_filter('init','em_init',1);

/**
 * This function will load an event into the variable during page initialization, provided an event_id is given in the url via GET or POST.
 * global $EM_Recurrences also holds global array of recurrence objects when loaded in this instance for performance
 * All functions (admin and public) can now work off this object rather than it around via arguments.
 * @return null
 */
function em_load_event(){
	global $EM_Recurrences, $booking;
	if (defined('EM_LOADED')) return;
	
	$EM_Recurrences = array();

	if( isset($_REQUEST['booking_id']) && is_numeric($_REQUEST['booking_id']) && !is_object($_REQUEST['booking_id']) ){
		$booking = Booking::get_by_id( absint($_REQUEST['booking_id']) );
	}

	define('EM_LOADED',true);
	
}

add_action('template_redirect', 'em_load_event', 1);
if(is_admin()){ add_action('init', 'em_load_event', 2); }


function em_map_meta_cap( $caps, $cap, $user_id, $args ) {
    if( !empty( $args[0]) ){
		/* Handle event reads */
		if ( 'edit_event' == $cap || 'delete_event' == $cap || 'read_event' == $cap ) {
			$post = get_post($args[0]);
			//check for revisions and deal with non-event post types
			if( !empty($post->post_type) && $post->post_type == 'revision' ) $post = get_post($post->post_parent);
			if( empty($post->post_type) || !in_array($post->post_type, array(EventPost::POST_TYPE, 'event-recurring')) ) return $caps;
			//continue with getting post type and assigning caps
			$event = \Contexis\Events\Models\Event::find_by_post($post);
			$post_type = get_post_type_object( $event->post_type );
			/* Set an empty array for the caps. */
			$caps = [];
			//Filter according to event caps
			switch( $cap ){
				case 'read_event':
					if ( 'private' != $event->post_status )
						$caps[] = 'read';
					elseif ( $user_id == $event->event_owner )
						$caps[] = 'read';
					else
						$caps[] = $post_type->cap->read_private_posts;
					break;
				case 'edit_event':
					if ( $user_id == $event->event_owner )
						$caps[] = $post_type->cap->edit_posts;
					else
						$caps[] = $post_type->cap->edit_others_posts;
					break;
				case 'delete_event':
					if ( $user_id == $event->event_owner )
						$caps[] = $post_type->cap->delete_posts;
					else
						$caps[] = $post_type->cap->delete_others_posts;
					break;
			}
		}
		if ( 'edit_recurring_event' == $cap || 'delete_recurring_event' == $cap || 'read_recurring_event' == $cap ) {
			$post = get_post($args[0]);
			//check for revisions and deal with non-event post types
			if( !empty($post->post_type) && $post->post_type == 'revision' ) $post = get_post($post->post_parent);
			if( empty($post->post_type) || $post->post_type != 'event-recurring' ) return $caps;
			//continue with getting post type and assigning caps
			$event = \Contexis\Events\Models\Event::find_by_post($post);
			$post_type = get_post_type_object( $event->post_type );
			/* Set an empty array for the caps. */
			$caps = [];
			//Filter according to recurring_event caps
			switch( $cap ){
				case 'read_recurring_event':
					if ( 'private' != $event->post_status )
						$caps[] = 'read';
					elseif ( $user_id == $event->event_owner )
						$caps[] = 'read';
					else
						$caps[] = $post_type->cap->read_private_posts;
					break;
				case 'edit_recurring_event':
					if ( $user_id == $event->event_owner )
						$caps[] = $post_type->cap->edit_posts;
					else
						$caps[] = $post_type->cap->edit_others_posts;
					break;
				case 'delete_recurring_event':
					if ( $user_id == $event->event_owner )
						$caps[] = $post_type->cap->delete_posts;
					else
						$caps[] = $post_type->cap->delete_others_posts;
					break;
			}
		}
		if ( 'edit_location' == $cap || 'delete_location' == $cap || 'read_location' == $cap ) {
			$post = get_post($args[0]);
			//check for revisions and deal with non-location post types
			if( !empty($post->post_type) && $post->post_type == 'revision' ) $post = get_post($post->post_parent);
			if( empty($post->post_type) || $post->post_type != LocationPost::POST_TYPE ) return $caps;
			//continue with getting post type and assigning caps
			$location = Location::find_by_post($post);
			$post_type = get_post_type_object( $location->post_type );
			/* Set an empty array for the caps. */
			$caps = [];
			//Filter according to location caps
			switch( $cap ){
				case 'read_location':
					if ( 'private' != $location->post_status )
						$caps[] = 'read';
					elseif ( $user_id == $location->location_owner )
						$caps[] = 'read';
					else
						$caps[] = $post_type->cap->read_private_posts;
					break;
				case 'edit_location':
					if ( $user_id == $location->location_owner )
						$caps[] = $post_type->cap->edit_posts;
					else
						$caps[] = $post_type->cap->edit_others_posts;
					break;
				case 'delete_location':
					if ( $user_id == $location->location_owner )
						$caps[] = $post_type->cap->delete_posts;
					else
						$caps[] = $post_type->cap->delete_others_posts;
					break;
			}
		}
    }
	/* Return the capabilities required by the user. */
	return $caps;
}
//add_filter( 'map_meta_cap', 'em_map_meta_cap', 10, 4 );
/**
 * Works much like <a href="http://codex.wordpress.org/Function_Reference/locate_template" target="_blank">locate_template</a>, except it takes a string instead of an array of templates, we only need to load one.
 * @param string $template_name
 * @param boolean $load
 * @uses locate_template()
 * @return string
 */
function em_locate_template( $template_name, $load=false, $the_args = array() ) {
	//First we check if there are overriding tempates in the child or parent theme
	$located = locate_template(array('plugins/events/'.$template_name));
	if( !$located ){
		$located = apply_filters('em_locate_template_default', $located, $template_name, $load, $the_args);
		if ( !$located && file_exists(__DIR__.'/templates/'.$template_name) ) {
			$located = __DIR__.'/templates/'.$template_name;
		}
	}
	$located = apply_filters('em_locate_template', $located, $template_name, $load, $the_args);
	if( $located && $load ){
		$the_args = apply_filters('em_locate_template_args_'.$template_name, $the_args, $located);
		if( is_array($the_args) ) extract($the_args);
		require_once($located);
	}
	return $located;
}



register_activation_hook( __FILE__,function() {
	update_option('dbem_flush_needed',1);
});

register_deactivation_hook( __FILE__,function() {
	global $wp_rewrite;
   	$wp_rewrite->flush_rules();
});



register_uninstall_hook(__DIR__ . '/classes/Install.php', '\Contexis\Events\Install::uninstall');


//cron functions - ran here since functions aren't loaded, scheduling done by gateways and other modules
/**
 * Adds a schedule according to EM
 * @param array $shcehules
 * @return array
 */
function em_cron_schedules($schedules){
	$schedules['em_minute'] = array(
		'interval' => 60,
		'display' => 'Every Minute'
	);
	return $schedules;
}
add_filter('cron_schedules','em_cron_schedules',10,1);




function em_register_blocks()
{
	
	$blocks = [
		'upcoming',
		'details',
		'details-audience',
		'details-date',
		'details-location',
		'details-price',
		'details-shutdown',
		'details-spaces',
		'details-time',
		'details-speaker',
		'booking'
	];

	foreach ($blocks as $block) {
		register_block_type(__DIR__ . '/build/blocks/' . $block);
	}
}

add_action('init', 'em_register_blocks');

