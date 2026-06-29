<?php

namespace Contexis\Events\Platform\Config;

use function DI\get;

return [
    get(\Contexis\Events\Booking\Infrastructure\Wordpress\BookingPaymentReturnRedirect::class),
    get(\Contexis\Events\Booking\Infrastructure\Wordpress\BookingPaymentLinkRedirect::class),
    get(\Contexis\Events\Payment\Infrastructure\Wordpress\ReconcilePendingTransactionsCron::class),
];
