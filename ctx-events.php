<?php

declare(strict_types=1);

/*
Plugin Name: Events
Plugin URI: https://github.com/gollenia/ctx-events
Description: Modern event and booking management for WordPress. Easily create events, manage attendees, track availability and handle payments
Version: 0.7.0
Requires at least: 7.1.0
Tested up to: 7.1.0
Requires PHP: 8.5
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Author: Thomas Gollenia
Author URI: https://github.com/gollenia/
Text Domain: ctx-events
Domain Path: /languages
*/

$wpUiDirectory = __DIR__ . '/build/wp-ui';
$wpUiBootstrap = $wpUiDirectory . '/bootstrap.php';
if (is_readable($wpUiBootstrap)) {
    (require $wpUiBootstrap)(__FILE__, $wpUiDirectory);
} else {
    add_action('admin_notices', static function (): void {
        if (!wp_script_is('wp-ui-v1', 'registered') && current_user_can('activate_plugins')) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Events is missing its wp-ui runtime. Reinstall the complete plugin package.', 'ctx-events') . '</p></div>';
        }
    });
}

require_once(plugin_dir_path(__FILE__) . '/vendor/autoload.php');

if (is_callable([Contexis\WpGitHubUpdater\ReleaseInfo::class, 'fromTag'])) {
    Contexis\WpGitHubUpdater\WordPressPluginUpdater::fromPluginFile(__FILE__)->registerHooks();
}

register_activation_hook(__FILE__, static function (): void {
    Contexis\Events\Platform\Bootstrap::init();
    flush_rewrite_rules(false);
});

add_action('init', function () {
    load_plugin_textdomain(
        'ctx-events',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
    do_action('qm/start', 'ctx-events');
    Contexis\Events\Platform\Bootstrap::init();
    do_action('qm/stop', 'ctx-events');
	
}, 10);
