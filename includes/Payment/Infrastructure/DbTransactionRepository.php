<?php
declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Payment\Domain\TransactionRepository;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Infrastructure\Mapper\TransactionMapper;
use Contexis\Events\Shared\Infrastructure\Contracts\Database;
use Contexis\Events\Shared\Infrastructure\Enums\DatabaseOutput;

final class DbTransactionRepository implements TransactionRepository
{
    public function __construct(private Database $db) {}

    public function save(Transaction $transaction): void
    {
        $table = TransactionMigration::getTableName();
        $externalId = $transaction->externalId;
        $hasExternalId = $externalId !== null && $externalId !== '';

        $details = [
            'bankData' => $transaction->bankData?->toArray(),
            'instructions' => $transaction->instructions !== '' ? $transaction->instructions : null,
            'checkoutUrl' => $transaction->checkoutUrl ? $transaction->checkoutUrl->toString() : null,
            'gatewayUrl' => $transaction->gatewayUrl ? $transaction->gatewayUrl->toString() : null,
        ];

        $data = [
            'external_id'      => $hasExternalId ? $externalId : null,
            'booking_id'       => $transaction->bookingId->toInt(),
            'amount'           => $transaction->amount->amountCents,
            'currency'         => $transaction->amount->currency->toString(),
            'gateway'          => $transaction->gateway,
            'status'           => $transaction->status->value,
            'transaction_date' => $transaction->createdAt->format('Y-m-d H:i:s'),
            'expires_at'       => $transaction->expiresAt?->format('Y-m-d H:i:s'),
            'details'          => wp_json_encode($details),
        ];

        if ($transaction->id !== null) {
            $this->db->update($table, $data, ['id' => $transaction->id->toInt()]);

            return;
        }

        if ($hasExternalId) {
            $existing = $this->findByExternalId($externalId);
            if ($existing !== null && $existing->id !== null) {
                $this->db->update($table, $data, ['id' => $existing->id->toInt()]);

                return;
            }
        }

        $inserted = $this->db->insert($table, $data);

        if ($inserted === false) {
            throw new \RuntimeException('Failed to save transaction.');
        }
    }

    public function findByExternalId(string $externalId): ?Transaction
    {
        if ($externalId === '') {
            return null;
        }

        $table = TransactionMigration::getTableName();
        $query = $this->db->prepare(
            "SELECT * FROM {$table} WHERE external_id = %s LIMIT 1",
            $externalId
        );

        $row = $this->db->getRow($query, DatabaseOutput::ARRAY_ASSOC);

        if (!is_array($row)) {
            return null;
        }

        return TransactionMapper::map($row);
    }

    public function findByBookingId(BookingId $bookingId): \Contexis\Events\Payment\Domain\TransactionCollection
    {
        $table = TransactionMigration::getTableName();
        $query = $this->db->prepare(
            "SELECT * FROM {$table} WHERE booking_id = %d ORDER BY transaction_date DESC, id DESC",
            $bookingId->toInt()
        );

        $rows = $this->db->getResults($query, DatabaseOutput::ARRAY_ASSOC);

        return \Contexis\Events\Payment\Domain\TransactionCollection::from(
            ...array_map(TransactionMapper::map(...), $rows)
        );
    }

    public function findByBookingIds(array $bookingIds): array
    {
        if ($bookingIds === []) {
            return [];
        }

        $ids = array_values(array_unique(array_map(
            static fn(BookingId $bookingId): int => $bookingId->toInt(),
            $bookingIds
        )));

        $placeholders = implode(', ', array_fill(0, count($ids), '%d'));
        $table = TransactionMigration::getTableName();
        $query = $this->db->prepare(
            "SELECT * FROM {$table} WHERE booking_id IN ($placeholders) ORDER BY booking_id ASC, transaction_date DESC, id DESC",
            ...$ids
        );

        $rows = $this->db->getResults($query, DatabaseOutput::ARRAY_ASSOC);
        $grouped = [];

        foreach ($rows as $row) {
            $bookingId = (int) ($row['booking_id'] ?? 0);
            $grouped[$bookingId][] = TransactionMapper::map($row);
        }

        $result = [];
        foreach ($ids as $id) {
            $result[$id] = \Contexis\Events\Payment\Domain\TransactionCollection::from(...($grouped[$id] ?? []));
        }

        return $result;
    }

    public function findLatestByBookingId(BookingId $bookingId): ?Transaction
    {
        $transactions = $this->findByBookingId($bookingId);
        $items = $transactions->toArray();

        if ($items === []) {
            return null;
        }

        return $items[0];
    }

    public function deleteByBookingId(BookingId $bookingId): void
    {
        $table = TransactionMigration::getTableName();
        $this->db->delete($table, ['booking_id' => $bookingId->toInt()]);
    }
}
