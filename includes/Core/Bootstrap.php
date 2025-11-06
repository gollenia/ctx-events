<?php

namespace Contexis\Events\Core;

use Contexis\Events\Infrastructure\PostTypes\PostTypeRegistrar;
use Contexis\Events\Infrastructure\Wordpress\Installer;
use Contexis\Events\Presentation\Admin\AdminMenu;
use Contexis\Events\Presentation\Admin\AdminService;
use Contexis\Events\Presentation\Controllers\RestRegistrar;
use DI\ContainerBuilder;

class Bootstrap
{
    public static function init(): void
    {

        Installer::init();

        $container = ContainerFactory::build();
        $container->get(PostTypeRegistrar::class)->hook();
        $container->get(RestRegistrar::class)->hook();
    }
}
