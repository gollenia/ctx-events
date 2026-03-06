<?php
declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Payment\Domain\TransactionRepository;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Shared\Infrastructure\Contracts\Database;

final class DbTransactionRepository implements TransactionRepository
{
    public function __construct(private Database $db) {}

    public function save(Transaction $transaction): void
    {
        $table = TransactionMigration::getTableName();
        $externalId = $transaction->externalId ?? 'offline-' . $transaction->bookingId->toInt() . '-' . time();

        $this->db->insert($table, [
            'external_id'      => $externalId,
            'booking_id'       => $transaction->bookingId->toInt(),
            'amount'           => $transaction->amount->amountCents,
            'currency'         => $transaction->amount->currency->toString(),
            'gateway'          => $transaction->gateway,
            'status'           => $transaction->status->value,
            'transaction_date' => $transaction->createdAt->format('Y-m-d H:i:s'),
            'details'          => wp_json_encode($transaction->bankData),
        ]);
    }

    public function findByExternalId(string $externalId): ?Transaction
    {
        // TODO: implement for webhook handling
        return null;
    }
}
