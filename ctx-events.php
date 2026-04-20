<?php

declare(strict_types=1);

/*
Plugin Name: Events
Plugin URI: https://github.com/gollenia/ctx-events
Description: Modern event and booking management for WordPress. Easily create events, manage attendees, track availability and handle payments
Version: 1.0.0
Requires at least: 6.8.0
Requires PHP: 8.5
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Author: Thomas Gollenia
Author URI: https://github.com/gollenia/
Text Domain: ctx-events
Domain Path: /languages
*/

require_once(plugin_dir_path(__FILE__) . '/vendor/autoload.php');

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

/**
 * Register Gutenberg blocks for the plugin. This function must be at root level, otherwise
 * the blocks won't be registered properly.
 */
function ctx_register_blocks(): void
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__ . '/build/editor/blocks')
    );

    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getFilename() !== 'block.json') {
            continue;
        }

        register_block_type($file->getPath());
    }
}

add_action('init', 'ctx_register_blocks');
