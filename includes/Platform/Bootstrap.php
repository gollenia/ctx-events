<?php
declare(strict_types=1);

namespace Contexis\Events\Platform;

use Contexis\Events\Platform\ContainerFactory;
use Contexis\Events\Platform\Wordpress\AdminRegistrar;
use Contexis\Events\Platform\Wordpress\Assets;
use Contexis\Events\Platform\Wordpress\BlockRegistrar;
use Contexis\Events\Platform\Wordpress\DatabaseMigration;
use Contexis\Events\Platform\Wordpress\HookRegistrar;
use Contexis\Events\Platform\Wordpress\OptionsMigration;
use Contexis\Events\Platform\Wordpress\PostTypeRegistrar;
use Contexis\Events\Platform\Wordpress\RestRegistrar;

class Bootstrap
{
	private const REGISTRARS = [
		Assets::class,
		BlockRegistrar::class,
		DatabaseMigration::class,
		OptionsMigration::class,
		PostTypeRegistrar::class,
		RestRegistrar::class,
		HookRegistrar::class,
		AdminRegistrar::class
	];

	private static ?\DI\Container $container = null;

	public static function init(): void
	{
		self::$container = ContainerFactory::build();

		foreach (self::REGISTRARS as $registrar) {
			self::$container->get($registrar)->hook();
		}
	}

	public static function container(): \DI\Container
	{
		if (self::$container === null) {
			throw new \RuntimeException('Container not initialized. Call Bootstrap::init() first.');
		}
		return self::$container;
	}
}
