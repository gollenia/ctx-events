<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Application\Contracts;

use Contexis\Events\Payment\Domain\Transaction;

interface PaymentQrGenerator
{
    /** Returns a data URI for the requested format. */
    public function generate(Transaction $transaction, string $reference, string $format = 'svg'): string;

    public function mimeType(string $format): string;
}
