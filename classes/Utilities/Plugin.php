<?php

namespace Contexis\Events\Utilities;

class Plugin {

	public static function get_plugin_version() {
		if (!function_exists('get_plugin_data')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/events/events.php');
		
		
		return $plugin_data['Version'];
	}

	public static function get_installed_version() {
		$stored_version = get_option('dbem_version', '0.0.0');

		if (preg_match('/^(\d)\.(\d{2})$/', $stored_version, $matches)) {
			$stored_version = "{$matches[1]}.{$matches[2][0]}.{$matches[2][1]}"; 
		}

		update_option('dbem_version', $stored_version);

		return $stored_version;
	}

	public static function set_installed_version($version) {
		if (preg_match('/^(\d)\.(\d{2})$/', $version, $matches)) {
			$version = "{$matches[1]}.{$matches[2][0]}.{$matches[2][1]}"; 
		}
		update_option('dbem_version', $version);
	}

	public static function get_plugin_dir() {
		return plugin_dir_path( dirname( __DIR__, 1 ) );
	}

	
}