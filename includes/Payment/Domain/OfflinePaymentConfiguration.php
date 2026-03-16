<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

interface OfflinePaymentConfiguration
{
    public function paymentTermInDays(): int;
}
