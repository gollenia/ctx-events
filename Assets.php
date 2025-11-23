<?php

namespace Contexis\Events;

use Contexis\Events\Intl\Price;
use Contexis\Events\Models\Booking;
use Contexis\Events\PostTypes\EventPost;
use Contexis\Events\PostTypes\LocationPost;
use Contexis\Events\Core\Utilities\Plugin;
use Contexis\Events\Platform\Wordpress\PluginInfo;

class Assets
{
    public static function init()
    {
        $instance = new self();
        add_action('init', [$instance, 'frontend_script']);
        add_action('init', [$instance, 'admin_script']);
        add_action('init', [$instance, 'editor_script']);

        return $instance;
    }

    public function frontend_script()
    {

        $script_asset = $this->getAssetData('frontend');
        if (!$script_asset) {
            return;
        }

        wp_enqueue_script(
            'ctx-events-frontend',
            plugins_url('/build/frontend.js', __FILE__),
            $script_asset['dependencies'],
            $script_asset['version']
        );

        wp_enqueue_style(
            'events-frontend-style',
            plugins_url('/build/style-frontend.css', __FILE__),
            [],
            $script_asset['version'],
            'all'
        );

        wp_set_script_translations('ctx-events-frontend', 'events', plugin_dir_path(__FILE__) . '/languages');

        wp_localize_script('ctx-events-frontend', 'eventBlocksLocalization', [
            "consent" => get_option("dbem_privacy_message", __('I consent to my personal data being stored and used as per the Privacy Policy', 'events')),
            "donation" => get_option("dbem_donation_message", __('I would like to support the event with a donation', 'events'))
        ]);
    }

    public function admin_script()
    {
        if (!is_admin()) {
            return;
        }

        $script_asset = $this->getAssetData('admin');

        wp_enqueue_script(
            'ctx-events-admin',
            plugins_url('/build/admin.js', __FILE__),
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );

        wp_set_script_translations('ctx-events-admin', 'events', PluginInfo::getPluginDir()  . '/languages');

        wp_register_style(
            'ctx-events-admin-style',
            plugins_url('/build/style-admin.css', __FILE__),
            [],
            $script_asset['version'],
            'all'
        );
    }

    public function editor_script()
    {
        $script_asset = $this->getAssetData('editor');
        if (!$script_asset) {
            return;
        }

        wp_enqueue_script(
            'ctx-events-editor',
            plugins_url('/build/editor.js', __FILE__),
            $script_asset['dependencies'],
            $script_asset['version']
        );

        wp_set_script_translations('ctx-events-editor', 'events', plugin_dir_path(__FILE__) . '/languages');

        wp_register_style(
            'ctx-events-editor-style',
            plugins_url('/build/style-editor.css', __FILE__),
            array(),
            $script_asset['version']
        );
    }

    private function getAssetData($asset)
    {
        $asset_path = PluginInfo::getPluginDir() . "/build/{$asset}.asset.php";
        if (!file_exists($asset_path)) {
            return null;
        }
        return require($asset_path);
    }
}
Assets::init();
