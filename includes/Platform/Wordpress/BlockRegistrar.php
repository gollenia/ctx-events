<?php
declare(strict_types=1);

namespace Contexis\Events\Platform\Wordpress;

use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class BlockRegistrar implements Registrar
{
	private const BLOCK_BUILD_PATH = '/build/editor/blocks';

	public function hook(): void
	{
		if (did_action('init')) {
			$this->registerBlocks();
			return;
		}

		add_action('init', [$this, 'registerBlocks']);
	}

	public function registerBlocks(): void
	{
		if (!function_exists('register_block_type')) {
			return;
		}

		$blocksPath = PluginInfo::getPluginDir(self::BLOCK_BUILD_PATH);
		if (!is_dir($blocksPath)) {
			return;
		}

		if (!wp_script_is('wp-ui-v1', 'registered')) {
			add_filter('block_type_metadata', static function (array $metadata): array {
				if (!in_array($metadata['name'] ?? '', ['ctx-events/upcoming', 'ctx-events/events-table'], true)) {
					return $metadata;
				}

				unset($metadata['viewScript']);
				$metadata['style'] = array_values(array_filter(
					(array) ($metadata['style'] ?? []),
					static fn (string $style): bool => !str_starts_with($style, 'wp-ui-v1-')
				));
				return $metadata;
			});
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($blocksPath, RecursiveDirectoryIterator::SKIP_DOTS)
		);

		foreach ($iterator as $file) {
			if (!$file->isFile() || $file->getFilename() !== 'block.json') {
				continue;
			}

			$block = register_block_type($file->getPathname());
			if ($block === false || $block->editor_script === null) {
				continue;
			}

			wp_set_script_translations(
				$block->editor_script,
				'ctx-events',
				PluginInfo::getPluginDir('/languages')
			);
		}
	}
}
