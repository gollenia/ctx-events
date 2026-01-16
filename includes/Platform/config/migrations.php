<?php

namespace Contexis\Events\Platform\Config;

use function DI\get;

return [
    get(\Contexis\Events\Booking\Infrastructure\BookingMigration::class),
    get(\Contexis\Events\Payment\Infrastructure\TransactionMigration::class),
    get(\Contexis\Events\Booking\Infrastructure\AttendeeMigration::class)
];