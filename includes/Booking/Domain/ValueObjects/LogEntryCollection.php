<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class LogEntryCollection extends Collection
{
	public function __construct(
        LogEntry ...$entries
    ) {
        parent::__construct($entries);
    }

    public function add(LogEntry $entry): self
    {
        return new self(...[...$this->items, $entry]);
    }
}
