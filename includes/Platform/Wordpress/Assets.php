<?php
declare(strict_types=1);

namespace Contexis\Events\Platform\Wordpress;

use Contexis\Events\Booking\Application\Contracts\BookingOptions;
use Contexis\Events\Platform\Wordpress\PluginInfo;
use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;

final class Assets implements Registrar
{
	private const ASSET_PATH = '/build';

	public function __construct(
		private readonly BookingOptions $bookingOptions
	) {}

	public function hook() : void
    {
        add_action('wp_enqueue_scripts', [$this, 'frontendScript']);
        add_action('admin_enqueue_scripts', [$this, 'adminScript']);
        add_action('enqueue_block_editor_assets', [$this, 'editorScript']);
    }

    public function frontendScript()
    {

        $script_asset = $this->getAssetData('frontend');
        if (!$script_asset) {
            return;
        }

        wp_enqueue_script(
            'ctx-events-frontend',
            PluginInfo::getPluginUrl(self::ASSET_PATH . '/frontend.js'),
            $script_asset['dependencies'],
            $script_asset['version']
        );

        wp_enqueue_style(
            'events-frontend-style',
            PluginInfo::getPluginUrl(self::ASSET_PATH . '/style-frontend.css'),
            [],
            $script_asset['version'],
            'all'
        );

        wp_set_script_translations('ctx-events-frontend', 'events', PluginInfo::getPluginDir('/languages'));

        wp_localize_script('ctx-events-frontend', 'eventBlocksLocalization', [
            "consent" => get_option("dbem_privacy_message", __('I consent to my personal data being stored and used as per the Privacy Policy', 'ctx-events')),
            "donation" => get_option("dbem_donation_message", __('I would like to support the event with a donation', 'ctx-events'))
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
            PluginInfo::getPluginUrl(self::ASSET_PATH . '/admin.js'),
            $script_asset['dependencies'] ?? [],
            $script_asset['version'],
            true
        );

        wp_set_script_translations('ctx-events-admin', 'events', PluginInfo::getPluginDir('/languages'));

        wp_enqueue_style(
            'ctx-events-admin-style',
            PluginInfo::getPluginUrl(self::ASSET_PATH . '/style-admin.css'),
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
            PluginInfo::getPluginUrl(self::ASSET_PATH . '/editor.js'),
            $script_asset['dependencies'],
            $script_asset['version']
        );

        wp_set_script_translations('ctx-events-editor', 'events', PluginInfo::getPluginDir('/languages'));

        wp_register_style(
            'ctx-events-editor-style',
            PluginInfo::getPluginUrl(self::ASSET_PATH . '/style-editor.css'),
            array(),
            $script_asset['version']
        );

		wp_enqueue_style(
            'ctx-events-editor-style-be',
            PluginInfo::getPluginUrl(self::ASSET_PATH . '/editor.css'),
            array(),
            $script_asset['version']
        );

		wp_localize_script('ctx-events-editor', 'eventEditorLocalization', [
            "bookingEnabled" => $this->bookingOptions->enabled(),
			"currency" => $this->bookingOptions->currency(),
        ]);
    }

    private function getAssetData($asset)
    {
        $asset_path = PluginInfo::getPluginDir(self::ASSET_PATH . "/{$asset}.asset.php");
        if (!file_exists($asset_path)) {
            return null;
        }
        return require($asset_path);
    }
}
