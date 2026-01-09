<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\ValueObjects;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final class LogEntryCollection extends Collection
{
	 public function __construct(
        LogEntry ...$entries
    ) {
        $this->items = $entries;
    }
}
