<?php

namespace Contexis\Events\Platform\Config;

use function DI\get;

return [
    get(\Contexis\Events\Payment\Infrastructure\Wordpress\ReconcilePendingTransactionsCron::class),
];
