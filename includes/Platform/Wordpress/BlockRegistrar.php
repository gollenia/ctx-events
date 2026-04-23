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

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($blocksPath, RecursiveDirectoryIterator::SKIP_DOTS)
		);

		foreach ($iterator as $file) {
			if (!$file->isFile() || $file->getFilename() !== 'block.json') {
				continue;
			}

			register_block_type($file->getPathname());
		}
	}
}
