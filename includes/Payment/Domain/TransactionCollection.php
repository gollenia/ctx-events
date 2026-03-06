<?php
declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class TransactionCollection extends Collection
{
    public function __construct(Transaction ...$transactions)
    {
        $this->items = $transactions;
    }
}
