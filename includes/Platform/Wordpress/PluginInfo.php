<?php
declare(strict_types=1);

namespace Contexis\Events\Platform\Wordpress;

use Contexis\Events\Event\Infrastructure\EventPost;

final class PluginInfo
{
    public static function getPluginVersion(): string
    {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/events/events.php');


        return $plugin_data['Version'];
    }

    public static function getInstalledVersion(): mixed
    {
        $stored_version = get_option('dbem_version', '7.0.0');

        if (preg_match('/^(\d)\.(\d{2})$/', $stored_version, $matches)) {
            $stored_version = "{$matches[1]}.{$matches[2][0]}.{$matches[2][1]}";
        }

        update_option('dbem_version', $stored_version);

        return $stored_version;
    }

    public static function setInstalledVersion(string $version): void
    {
        if (preg_match('/^(\d)\.(\d{2})$/', $version, $matches)) {
            $version = "{$matches[1]}.{$matches[2][0]}.{$matches[2][1]}";
        }
        update_option('dbem_version', $version);
    }

    public static function getPluginDir(string $trailing = ''): string
    {
        $path = plugin_dir_path(dirname(__DIR__, 2)) . $trailing;
        return preg_replace('#(?<!:)//+#', '/', $path);
    }

    public static function getPluginUrl(string $trailing = ''): string
    {
        $url = plugin_dir_url(dirname(__DIR__, 2)) . $trailing;
        return preg_replace('#(?<!:)//+#', '/', $url);
    }

    public static function getAdminUrl(): string
    {
        return admin_url('edit.php?post_type=' . EventPost::POST_TYPE);
    }
}
