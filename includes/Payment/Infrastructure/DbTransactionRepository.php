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
        $externalId = $transaction->externalId ?? 'offline-' . $transaction->bookingId->toInt() . '-' . time();
        $details = [
            'bankData' => $transaction->bankData?->toArray(),
            'instructions' => $transaction->instructions !== '' ? $transaction->instructions : null,
            'checkoutUrl' => $transaction->checkoutUrl ? (string) $transaction->checkoutUrl : null,
            'gatewayUrl' => $transaction->gatewayUrl ? (string) $transaction->gatewayUrl : null,
        ];

        $data = [
            'external_id'      => $externalId,
            'booking_id'       => $transaction->bookingId->toInt(),
            'amount'           => $transaction->amount->amountCents,
            'currency'         => $transaction->amount->currency->toString(),
            'gateway'          => $transaction->gateway,
            'status'           => $transaction->status->value,
            'transaction_date' => $transaction->createdAt->format('Y-m-d H:i:s'),
            'details'          => wp_json_encode($details),
        ];

        if ($transaction->id !== null) {
            $this->db->update($table, $data, ['id' => $transaction->id->toInt()]);

            return;
        }

        $existing = $this->findByExternalId($externalId);
        if ($existing !== null && $existing->id !== null) {
            $this->db->update($table, $data, ['id' => $existing->id->toInt()]);

            return;
        }

        $this->db->insert($table, $data);
    }

    public function findByExternalId(string $externalId): ?Transaction
    {
        $table = TransactionMigration::getTableName();
        $query = $this->db->prepare(
            "SELECT * FROM {$table} WHERE external_id = %s LIMIT 1",
            $externalId
        );

        $row = $this->db->getRow($query, DatabaseOutput::ARRAY_A);

        if (!is_array($row)) {
            return null;
        }

        return TransactionMapper::map($row);
    }

    public function findLatestByBookingId(BookingId $bookingId): ?Transaction
    {
        $table = TransactionMigration::getTableName();
        $query = $this->db->prepare(
            "SELECT * FROM {$table} WHERE booking_id = %d ORDER BY created_at DESC, id DESC LIMIT 1",
            $bookingId->toInt()
        );

        $row = $this->db->getRow($query, DatabaseOutput::ARRAY_A);

        if (!is_array($row)) {
            return null;
        }

        return TransactionMapper::map($row);
    }

    public function deleteByBookingId(BookingId $bookingId): void
    {
        $table = TransactionMigration::getTableName();
        $this->db->delete($table, ['booking_id' => $bookingId->toInt()]);
    }
}
