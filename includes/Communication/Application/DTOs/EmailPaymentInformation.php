<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\DTOs;

use Contexis\Events\Payment\Domain\Transaction;

final readonly class EmailPaymentInformation
{
    public function __construct(
        public Transaction $transaction,
        public string $gatewayTitle,
        public ?string $qrCodePng = null,
    ) {
    }
}
