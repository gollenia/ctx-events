<?php
declare(strict_types=1);

namespace Contexis\Events\Platform;

use Contexis\Events\Platform\ContainerFactory;
use Contexis\Events\Platform\Wordpress\Admin\AdminMenu;
use Contexis\Events\Platform\Wordpress\DatabaseRegistrar;
use Contexis\Events\Platform\Wordpress\OptionsRegistrar;
use Contexis\Events\Platform\Wordpress\PostTypeRegistrar;
use Contexis\Events\Platform\Wordpress\RestRegistrar;
use Contexis\Events\Shared\Presentation\OptionsPage;

class Bootstrap
{
    public static function init(): void
    {
        Assets::init();
        $container = ContainerFactory::build();
        $container->get(DatabaseRegistrar::class)->hook();
        $container->get(OptionsRegistrar::class)->hook();
        $container->get(PostTypeRegistrar::class)->hook();
        $container->get(RestRegistrar::class)->hook();
        $container->get(AdminMenu::class)->hook();
    }
}
