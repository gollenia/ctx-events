<?php

namespace Contexis\Events\Platform;

use Contexis\Events\Platform\ContainerFactory;
use Contexis\Events\Platform\Wordpress\Installer;
use Contexis\Events\Platform\Wordpress\PostTypeRegistrar;
use Contexis\Events\Platform\Wordpress\RestRegistrar;

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
