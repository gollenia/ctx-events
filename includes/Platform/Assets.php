<?php
declare(strict_types=1);

namespace Contexis\Events\Platform;

use Contexis\Events\Platform\Wordpress\PluginInfo;

final class Assets
{
    public static function init()
    {
        $instance = new self();
        add_action('init', [$instance, 'frontendScript']);
        add_action('init', [$instance, 'adminScript']);
        add_action('init', [$instance, 'editorScript']);

        return $instance;
    }

    public function frontendScript()
    {

        $script_asset = $this->getAssetData('frontend');
        if (!$script_asset) {
            return;
        }

        wp_enqueue_script(
            'ctx-events-frontend',
            PluginInfo::getPluginUrl('/build/frontend.js'),
            $script_asset['dependencies'],
            $script_asset['version']
        );

        wp_enqueue_style(
            'events-frontend-style',
            PluginInfo::getPluginUrl('/build/style-frontend.css'),
            [],
            $script_asset['version'],
            'all'
        );

        wp_set_script_translations('ctx-events-frontend', 'events', PluginInfo::getPluginDir('/languages'));

        wp_localize_script('ctx-events-frontend', 'eventBlocksLocalization', [
            "consent" => get_option("dbem_privacy_message", __('I consent to my personal data being stored and used as per the Privacy Policy', 'events')),
            "donation" => get_option("dbem_donation_message", __('I would like to support the event with a donation', 'events'))
        ]);
    }

    public function adminScript()
    {
        if (!is_admin()) {
            return;
        }

        $script_asset = $this->getAssetData('admin');
        wp_enqueue_style('wp-components');
        wp_enqueue_style('wp-preferences');
        wp_enqueue_script(
            'ctx-events-admin',
            PluginInfo::getPluginUrl('/build/admin.js'),
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );

        wp_set_script_translations('ctx-events-admin', 'events', PluginInfo::getPluginDir('/languages'));

        wp_enqueue_style(
            'ctx-events-admin-style',
            PluginInfo::getPluginUrl('/build/style-admin.css'),
            [],
            $script_asset['version'],
            'all'
        );
    }

    public function editorScript()
    {
        $script_asset = $this->getAssetData('editor');
        if (!$script_asset) {
            return;
        }

        wp_enqueue_script(
            'ctx-events-editor',
            PluginInfo::getPluginUrl('/build/editor.js'),
            $script_asset['dependencies'],
            $script_asset['version']
        );

        wp_set_script_translations('ctx-events-editor', 'events', PluginInfo::getPluginDir('/languages'));

        wp_register_style(
            'ctx-events-editor-style',
            PluginInfo::getPluginUrl('/build/style-editor.css'),
            array(),
            $script_asset['version']
        );
    }

    private function getAssetData($asset)
    {
        $asset_path = PluginInfo::getPluginDir("/build/{$asset}.asset.php");
        if (!file_exists($asset_path)) {
            return null;
        }
        return require($asset_path);
    }
}
