<?php

namespace Contexis\Events\Platform\Wordpress;

class Installer
{
    public static function init(): void
    {
        register_activation_hook(__FILE__, [Migration::class, 'migrate']);
        add_action('plugins_loaded', [Migration::class, 'migrate']);
    }
}
