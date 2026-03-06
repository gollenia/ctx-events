<?php
declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

interface TransactionRepository
{
    public function save(Transaction $transaction): void;

    public function findByExternalId(string $externalId): ?Transaction;
}
