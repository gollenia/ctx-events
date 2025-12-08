<?php
declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

final class Transaction
{
    public function __construct(
        public readonly TransactionId $id,
        public readonly Booking $bookingId,
        public readonly Price $price,
        public readonly string $gateway,
        public readonly string $status,
        public readonly ?string $gatewayTransactionId,
        public readonly ?string $checkoutUrl,
        public readonly \DateTimeImmutable $createdAt
    ) {
    }
}
