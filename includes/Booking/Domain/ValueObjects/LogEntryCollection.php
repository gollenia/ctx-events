<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class LogEntryCollection extends Collection
{
	 public function __construct(
        LogEntry ...$entries
    ) {
        $this->items = $entries;
    }
}
