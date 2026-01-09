<?php
declare(strict_types=1);

namespace Contexis\Events\Platform;

use Contexis\Events\Platform\ContainerFactory;
use Contexis\Events\Platform\Wordpress\Admin\AdminMenu;
use Contexis\Events\Platform\Wordpress\AdminRegistrar;
use Contexis\Events\Platform\Wordpress\Assets;
use Contexis\Events\Platform\Wordpress\DatabaseMigration;
use Contexis\Events\Platform\Wordpress\OptionsMigration;
use Contexis\Events\Platform\Wordpress\PostTypeRegistrar;
use Contexis\Events\Platform\Wordpress\RestRegistrar;

class Bootstrap
{
	private const REGISTRARS = [
		Assets::class,
        DatabaseMigration::class,
        OptionsMigration::class,
        PostTypeRegistrar::class,
        RestRegistrar::class,
        AdminMenu::class,
        AdminRegistrar::class
    ];

    public static function init(): void
    {
        $container = ContainerFactory::build();

        foreach (self::REGISTRARS as $registrar) {
            $container->get($registrar)->hook();
        }
    }
}
