<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure\Mapper;

use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Payment\Domain\TransactionId;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

final class TransactionMapper
{
    public static function map(array $row): Transaction
    {
        return new Transaction(
            id: TransactionId::from((int) $row['id']),
            bookingId: BookingId::from((int) $row['booking_id']),
            amount: Price::from((int) $row['amount'], Currency::fromCode($row['currency'] ?? 'EUR')),
            gateway: $row['gateway'],
            status: TransactionStatus::from((int) $row['status']),
            externalId: $row['external_id'] ?? null,
            bankData: null,
            createdAt: new \DateTimeImmutable($row['created_at']),
        );
    }
}
