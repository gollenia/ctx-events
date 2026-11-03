<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\DTOs;

use Contexis\Events\Communication\Application\DTOs\BookingEmailResult;
use Contexis\Events\Payment\Domain\Transaction;

final readonly class SendBookingPaymentLinkResponse
{
    public function __construct(
        public Transaction $transaction,
        public BookingEmailResult $emailResult,
    ) {
    }
}
