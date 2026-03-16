<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Payment\Application\Contracts\FindReconcilableTransactions;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Infrastructure\Mapper\TransactionMapper;
use Contexis\Events\Shared\Infrastructure\Contracts\Database;
use Contexis\Events\Shared\Infrastructure\Enums\DatabaseOutput;

final class DbReconcilableTransactionFinder implements FindReconcilableTransactions
{
    public function __construct(private Database $db)
    {
    }

    public function findPendingForReconciliation(\DateTimeImmutable $now, \DateTimeImmutable $staleBefore): array
    {
        $table = TransactionMigration::getTableName();
        $sql = $this->db->prepare(
            "SELECT * FROM {$table}
             WHERE status = %d
               AND (
                    (expires_at IS NOT NULL AND expires_at <= %s)
                    OR created_at <= %s
               )
             ORDER BY created_at ASC, id ASC",
            TransactionStatus::PENDING->value,
            $now->format('Y-m-d H:i:s'),
            $staleBefore->format('Y-m-d H:i:s')
        );

        $rows = $this->db->getResults($sql, DatabaseOutput::ARRAY_ASSOC);

        return array_map(TransactionMapper::map(...), $rows);
    }
}
