<?php

namespace Contexis\Events\Domain\Models;

use Contexis\Events\Domain\ValueObjects\Id\BookingId;
use Contexis\Events\Domain\ValueObjects\Id\TransactionId;
use Contexis\Events\Domain\ValueObjects\Price;

final class Transaction
{
    public function __construct(
        public readonly TransactionId $id,
        public readonly BookingId $bookingId,
        public readonly Price $price,
        public readonly string $gateway,
        public readonly string $status,
        public readonly ?string $gatewayTransactionId,
        public readonly ?string $checkoutUrl,
        public readonly \DateTimeImmutable $createdAt
    ) {
    }
}
