<?php
/*
Plugin Name: Events
Plugin URI: https://github.com/gollenia/events
Description: Event registration and booking management for WordPress. Recurring events, locations, ical, booking registration and more!
Version: 7.0.0
Requires at least: 6.8.0
Requires PHP: 8.4
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Author: Thomas Gollenia
Author URI: https://github.com/gollenia/events
Text Domain: events
Domain Path: /languages
*/


use Contexis\Events\Models\{
	Booking,
	Event,
	Location,
	Speaker
};

use Contexis\Events\PostTypes\{
	CouponPost,
	EventPost,
	LocationPost,
	SpeakerPost,
	FormPost
};

use Contexis\Events\Admin\{
	CouponsAdmin,
	EventAdmin,
	LocationAdmin,
	SpeakerAdmin,
	RecurringEventAdmin,
    SidebarMenu
};

use Contexis\Events\Controllers\BookingController;
use Contexis\Events\Collections\EventCollection;
use Contexis\Events\Controllers\CouponController;
use Contexis\Events\Controllers\EventController;
use Contexis\Events\Controllers\GatewayController;
use Contexis\Events\Core\Bootstrap;
use Contexis\Events\Emails\Mailer;
use Contexis\Events\Export\BookingExport;
use Contexis\Events\Forms\UserFields;
use Contexis\Events\Payment\GatewayCollection;
use Contexis\Events\Core\Utilities\Plugin;

if(!defined('ABSPATH')) {
	exit;
}

require_once( plugin_dir_path( __FILE__ ) . '/vendor/autoload.php');

register_activation_hook( __FILE__, '\Contexis\Events\Install::activate_plugin' );


function em_load_textdomain() {
	load_plugin_textdomain('events', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugin_loaded', 'em_load_textdomain', 10 );

Bootstrap::init();
EventPost::init();
LocationPost::init();
SpeakerPost::init();
FormPost::init();
CouponPost::init();
GatewayController::init();
SidebarMenu::init();
BookingController::init();
CouponController::init();
EventController::init();
CouponsAdmin::init();

require_once __DIR__ . '/Assets.php';


BookingExport::init();

require_once __DIR__ . '/classes/PostTypes/RecurringEventPost.php';

require_once __DIR__ . '/classes/Notices.php';
//require_once __DIR__ . '/classes/Permalinks.php';

//Admin Files
if( is_admin() ){
	require_once __DIR__ . '/classes/Forms/FormPostAdmin.php';
	
	require_once __DIR__ . '/admin/em-bookings.php';
	require_once __DIR__ . '/admin/em-docs.php';
	require_once __DIR__ . '/admin/em-help.php';
	require_once __DIR__ . '/admin/em-options.php';
	require_once __DIR__ . '/admin/em-dashboard.php';
	require_once __DIR__ . '/classes/Admin/SpeakerAdmin.php';
	require_once __DIR__ . '/classes/Admin/EventAdmin.php';
	require_once __DIR__ . '/classes/Admin/RecurringEventAdmin.php';
	require_once __DIR__ . '/classes/Admin/LocationAdmin.php';
	require_once __DIR__ . '/classes/Admin/CouponsAdmin.php';
	require_once __DIR__ . '/admin/bookings/em-events.php';
}

require_once __DIR__ . '/classes/Export/Export.php';

require_once __DIR__ . '/classes/Payment/GatewayService.php';
require_once __DIR__ . '/classes/Forms/BookingForm.php';
require_once __DIR__ . '/classes/Emails/Emails.php';


global $wpdb;
$prefix = $wpdb->prefix;

define('EM_META_TABLE',$prefix.'em_meta'); //TABLE NAME
define('EM_BOOKINGS_TABLE',$prefix.'em_bookings'); //TABLE NAME
define('EM_EMAIL_QUEUE_TABLE', $wpdb->prefix.'em_email_queue'); //TABLE NAME


function em_load_event(){
	global $booking;
	if (defined('EM_LOADED')) return;

	if( isset($_REQUEST['booking_id']) && is_numeric($_REQUEST['booking_id']) ){
		$booking = Booking::get_by_id( absint($_REQUEST['booking_id']) );
	}

	define('EM_LOADED',true);
	
}

add_action('template_redirect', 'em_load_event', 1);
if(is_admin()){ add_action('init', 'em_load_event', 2); }


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

function em_cron_schedules($schedules) {
	$schedules['em_minute'] = array(
		'interval' => 60,
		'display' => 'Every Minute'
	);
	return $schedules;
}
add_filter('cron_schedules','em_cron_schedules',10,1);

function em_register_blocks() : void {
	
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
