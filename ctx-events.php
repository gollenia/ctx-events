<?php

declare(strict_types=1);

/*
Plugin Name: Events
Plugin URI: https://github.com/gollenia/ctx-events
Description: Modern event and booking management for WordPress. Easily create events, manage attendees, track availability and handle payments
Version: 0.7.1
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
    $plugin = Contexis\WpGitHubUpdater\PluginMetadata::fromPluginFile(__FILE__);
    $repository = Contexis\WpGitHubUpdater\GitHubRepository::fromPluginUri($plugin->data['PluginURI']);
    $releaseProvider = Contexis\WpGitHubUpdater\GitHubReleaseProvider::forPlugin($repository, $plugin);
    (new Contexis\WpGitHubUpdater\WordPressPluginUpdater($plugin, $repository, $releaseProvider))->registerHooks();

    // Updater 0.4.1 builds the package URL from the normalized version.
    $useReleaseTagForPackage = static function (mixed $updates) use ($plugin, $repository, $releaseProvider): mixed {
        $update = $updates->response[$plugin->pluginSlug] ?? null;
        if (!isset($update->new_version, $update->package)) {
            return $updates;
        }

        $release = $releaseProvider->getLatestRelease();
        if ($update->new_version === $release->version
            && $update->package === $repository->downloadPackageUrl($release->version, $plugin->packageName)
        ) {
            $update->package = $repository->downloadPackageUrl($release->tag, $plugin->packageName);
        }

        return $updates;
    };
    add_filter('site_transient_update_plugins', $useReleaseTagForPackage, 11);
    add_filter('pre_set_site_transient_update_plugins', $useReleaseTagForPackage, 11);
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
