<?php
declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

use Contexis\Events\Booking\Domain\ValueObjects\BookingId;

interface TransactionRepository
{
    public function save(Transaction $transaction): void;

    public function findByExternalId(string $externalId): ?Transaction;

    public function findLatestByBookingId(BookingId $bookingId): ?Transaction;

    public function deleteByBookingId(BookingId $bookingId): void;
}
