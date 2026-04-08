<?php

namespace Contexis\Events\Platform\Config;

use function DI\get;

return [
    get(\Contexis\Events\Event\Infrastructure\RegisterEventIcons::class),
    get(\Contexis\Events\Shared\Infrastructure\Icons\IconRegistryBootstrap::class),
    get(\Contexis\Events\Payment\Infrastructure\Wordpress\ReconcilePendingTransactionsCron::class),
];
