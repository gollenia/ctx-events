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

use Contexis\Events\Core\Bootstrap;

if(!defined('ABSPATH')) {
	exit;
}

require_once( plugin_dir_path( __FILE__ ) . '/vendor/autoload.php');
require_once __DIR__ . '/Assets.php';


add_action( 'plugin_loaded', function() {
	load_plugin_textdomain('events', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	Bootstrap::init();
}, 10 );

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
		register_block_type(__DIR__ . '/assets/build/blocks/' . $block);
	}
}

add_action('init', 'em_register_blocks');
