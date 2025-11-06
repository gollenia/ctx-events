<?php

namespace Contexis\Events\Domain\Models;

use Contexis\Events\Domain\ValueObjects\Id\TransactionId;

final class Transaction
{
    public function __construct(
        public readonly TransactionId $id,
        public readonly string $booking_id,
        public readonly int $amount_in_cents,
        public readonly string $currency,
        public readonly string $gateway,
        public readonly string $status,
        public readonly ?string $gateway_transaction_id,
        public readonly ?string $checkout_url,
        public readonly \DateTimeImmutable $created_at
    ) {
    }
}
