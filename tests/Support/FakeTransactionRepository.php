<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Payment\Domain\TransactionRepository;

final class FakeTransactionRepository implements TransactionRepository
{
    /** @var array<string, Transaction> */
    private array $transactionsByExternalId = [];

    /** @var array<int, Transaction[]> */
    private array $transactionsByBookingId = [];

    public static function empty(): self
    {
        return new self();
    }

    public static function withTransactions(Transaction ...$transactions): self
    {
        $repository = new self();

        foreach ($transactions as $transaction) {
            $repository->save($transaction);
        }

        return $repository;
    }

    public function save(Transaction $transaction): void
    {
        $externalId = (string) $transaction->externalId;
        $bookingId = $transaction->bookingId->toInt();

        $this->transactionsByExternalId[$externalId] = $transaction;

        if (!isset($this->transactionsByBookingId[$bookingId])) {
            $this->transactionsByBookingId[$bookingId] = [];
        }

        $updated = false;

        foreach ($this->transactionsByBookingId[$bookingId] as $index => $existingTransaction) {
            if ((string) $existingTransaction->externalId === $externalId) {
                $this->transactionsByBookingId[$bookingId][$index] = $transaction;
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $this->transactionsByBookingId[$bookingId][] = $transaction;
        }
    }

    public function findByExternalId(string $externalId): ?Transaction
    {
        return $this->transactionsByExternalId[$externalId] ?? null;
    }

    public function findLatestByBookingId(BookingId $bookingId): ?Transaction
    {
        $transactions = $this->transactionsByBookingId[$bookingId->toInt()] ?? [];

        if ($transactions === []) {
            return null;
        }

        return $transactions[array_key_last($transactions)];
    }

    public function deleteByBookingId(BookingId $bookingId): void
    {
        $transactions = $this->transactionsByBookingId[$bookingId->toInt()] ?? [];

        foreach ($transactions as $transaction) {
            unset($this->transactionsByExternalId[(string) $transaction->externalId]);
        }

        unset($this->transactionsByBookingId[$bookingId->toInt()]);
    }
}
