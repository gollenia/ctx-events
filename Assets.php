<?php

namespace Contexis\Events;

use Contexis\Events\Intl\Price;
use Contexis\Events\Models\Booking;
use Contexis\Events\PostTypes\EventPost;
use Contexis\Events\PostTypes\LocationPost;
use Contexis\Events\Core\Utilities\Plugin;

class Assets {

	public static function init(){
		$instance = new self;
		add_action('init', [$instance, 'frontend_script']);
		add_action('init', [$instance, 'booking_script']);
		add_action('init', [$instance, 'editor_script']);
		add_action('admin_enqueue_scripts', [$instance,'admin_enqueue']);
		add_action('admin_head', [$instance,'shared_values']);
		add_action('wp_head', [$instance,'shared_values']);
		return $instance;
	}

	/*
	 * Enqueues script for Upcoming and Featured Blocks
	 */
	public function frontend_script() {

		$script_asset_path = Plugin::get_plugin_dir() . "/build/frontend.asset.php";
		$booking_asset_path = Plugin::get_plugin_dir() . "/build/booking.asset.php";
		if ( ! file_exists( $script_asset_path ) || ! file_exists( $booking_asset_path ) ) {
			return;
		}
		
		$script_asset = require( $script_asset_path );
		wp_enqueue_script(
			'events-block-frontend',
			plugins_url( '/build/frontend.js', __FILE__ ),
			$script_asset['dependencies'],
			$script_asset['version']
		);

		wp_enqueue_style(
			'events-frontend-style',
			plugins_url( '/build/style-frontend.css', __FILE__ ),
			[],
			$script_asset['version'],
			'all'
		);

		wp_set_script_translations( 'events-block-frontend', 'events', plugin_dir_path( __FILE__ ) . '/languages' );

		wp_localize_script('events-block-frontend', 'eventBlocksLocalization', [
			'locale' => str_replace('_', '-', get_locale()),
			'rest_url' => get_rest_url(null, 'events/v2/events'),
			'current_id' => get_the_ID(),
			'event_nonce' => wp_create_nonce('events'),
		]);
	
	}

	/*
	 * Enqueues script for shared values
	 */
	public function shared_values() {
		echo "<script>window.ContexisEvents = " . json_encode([
			'nonce' => wp_create_nonce('ctx-events'),
			'url' => rest_url('events/v2'),
			'is_admin' => is_admin()
		]) . ";</script>";
	}


	/*
	 * Enqueues script for Booking Block
	 */
	public function booking_script() {

		$script_asset_path =Plugin::get_plugin_dir() . "/build/booking.asset.php";
		
		if ( ! file_exists( $script_asset_path ) ) return;
		
		$script_asset = require( $script_asset_path );

		wp_register_script(
			'booking-view',
			plugins_url('/build/booking.js', __FILE__),
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations('booking-view', 'events', Plugin::get_plugin_dir()  . '/languages');

		wp_register_style(
			'booking-style',
			plugins_url('/build/style-booking.css', __FILE__),
			[],
			$script_asset['version'],
			'all'
		);

		wp_localize_script('booking-view', 'eventBookingLocalization', [
			"consent" => get_option("dbem_privacy_message", __('I consent to my personal data being stored and used as per the Privacy Policy', 'events')),
			"donation" => get_option("dbem_donation_message", __('I would like to support the event with a donation', 'events')),
			"currency" => new \Contexis\Events\Intl\Price(0)->get_currency_code(),
			"locale" => str_replace('_', '-', get_locale()),
		]);
	}


	/*
	 * Enqueues script for Editor
	 */
	public function editor_script() {
		
		$script_asset_path = Plugin::get_plugin_dir() . "/build/index.asset.php";
		if ( ! file_exists( $script_asset_path ) ) return;
		
		$script_asset = require( $script_asset_path );
		wp_enqueue_script(
			'events-block-editor',
			plugins_url( '/build/index.js', __FILE__ ),
			$script_asset['dependencies'],
			$script_asset['version']
		);



		wp_enqueue_script(
			'events-gateway-admin',
			plugins_url( '/build/gateways.js', __FILE__ ),
			$script_asset['dependencies'],
			$script_asset['version']
		);

		wp_set_script_translations( 'events-block-editor', 'events', plugin_dir_path( __FILE__ ) . '/languages' );

		wp_localize_script('events-block-editor', 'eventBlocksLocalization', [
			'locale' => str_replace('_', '-',get_locale()),
			'rest_url' => get_rest_url(null, 'events/v2/events'),
			'countries' => \Contexis\Events\Intl\Countries::get(),
			'default_country' => get_option('dbem_location_default_country'),
			'currency' => get_option('dbem_bookings_currency'),
			'bookings_enabled' => Booking::booking_enabled(),
		]);

		wp_register_style(
			'events-block-style',
			plugins_url( '/build/style-index.css', __FILE__ ),
			array(),
			$script_asset['version']
		);

		wp_register_style(
			'events-block-editor-style',
			plugins_url( '/build/index.css', __FILE__ ),
			array(),
			$script_asset['version']
		);
	}


	
	
	public function admin_enqueue( ){
		$version = Plugin::get_plugin_version();
		wp_enqueue_script('events-manager', plugins_url('/build/events-manager.js',__FILE__), array('jquery', 'jquery-ui-core','jquery-ui-widget','jquery-ui-position','jquery-ui-sortable','jquery-ui-datepicker','jquery-ui-autocomplete','jquery-ui-dialog','wp-color-picker'), $version);		
		wp_enqueue_script('events-admin-script', plugins_url('/build/admin.js',__FILE__), array('jquery', 'wp-api', 'wp-i18n', 'wp-components', 'wp-element' ), $version);		
		wp_enqueue_style('events-admin', plugins_url('/build/admin.css',__FILE__), array('wp-components'), $version);
		wp_enqueue_style('events-admin-booking', plugins_url('/build/style-admin.css',__FILE__), array(), $version);
		$this->localize_admin_script();
		wp_set_script_translations( 'events-admin-script', 'events', plugin_dir_path( __FILE__ ) . '/languages' );
	}

	/**
	 * Localize the script vars that require PHP intervention, removing the need for inline JS.
	 */
	public function localize_admin_script(){
		global $em_localized_js;
		
		//Localize
		$em_localized_js = array(
			'firstDay' => get_option('start_of_week'),
			'_wpnonce' => wp_create_nonce('events'),
			'ui_css' => plugins_url('build/jquery-ui.min.css', __FILE__),
			'is_ssl' => is_ssl(),
		);

		//booking-specific stuff
		if( get_option('dbem_rsvp_enabled') ){
		    $em_localized_js = array_merge($em_localized_js, array(
				'bookings_export_save' => __('Export Bookings','events'),
				'bookings_settings_save' => __('Save Settings','events'),
				'booking_delete' => __("Are you sure you want to delete?",'events'),
		    	'booking_offset' => 30,
			));		
		}
		$em_localized_js['cache'] = defined('WP_CACHE') && WP_CACHE;
		
		//logged in messages that visitors shouldn't need to see
		if( is_user_logged_in() ){
		    if( get_option('dbem_recurrence_enabled') ){
		    	if( !empty($_REQUEST['action']) && ($_REQUEST['action'] == 'edit' || $_REQUEST['action'] == 'event_save') ){
					$em_localized_js['event_reschedule_warning'] = __('Are you sure you want to continue?', 'events') .PHP_EOL;
					$em_localized_js['event_reschedule_warning'] .= __('Modifications to event times will cause all recurrences of this event to be deleted and recreated, previous bookings will be deleted.', 'events');
					$em_localized_js['event_recurrence_overwrite'] = __('Are you sure you want to continue?', 'events') .PHP_EOL;
					$em_localized_js['event_recurrence_overwrite'] .= __( 'Modifications to recurring events will be applied to all recurrences and will overwrite any changes made to those individual event recurrences.', 'events') .PHP_EOL;
					$em_localized_js['event_recurrence_overwrite'] .= __( 'Bookings to individual event recurrences will be preserved if event times and ticket settings are not modified.', 'events');
					$em_localized_js['event_recurrence_bookings'] = __('Are you sure you want to continue?', 'events') .PHP_EOL;
					$em_localized_js['event_recurrence_bookings'] .= __('Modifications to event tickets will cause all bookings to individual recurrences of this event to be deleted.', 'events');
		    	}
				$em_localized_js['event_detach_warning'] = __('Are you sure you want to detach this event? By doing so, this event will be independent of the recurring set of events.', 'events');
				$delete_text = ( !EMPTY_TRASH_DAYS ) ? __('This cannot be undone.','events'):__('All events will be moved to trash.','events');
				$em_localized_js['delete_recurrence_warning'] = __('Are you sure you want to delete all recurrences of this event?', 'events').' '.$delete_text;
		    }
		}
		//load admin/public only vars
		if( is_admin() ){
			$em_localized_js['event_post_type'] = EventPost::POST_TYPE;
			$em_localized_js['location_post_type'] = LocationPost::POST_TYPE;
			if( !empty($_GET['page']) && $_GET['page'] == 'events-options' ){
			    $em_localized_js['close_text'] = __('Collapse All','events');
			    $em_localized_js['open_text'] = __('Expand All','events');
			}
			$em_localized_js["currency"] = new Price(0)->get_currency_code();
			$em_localized_js["locale"] = str_replace('_', '-', get_locale());
		}		
		
		wp_localize_script('events-manager','EM', apply_filters('em_wp_localize_script', $em_localized_js));
	}


}
Assets::init();

