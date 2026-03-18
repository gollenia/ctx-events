<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class LogEntryCollection extends Collection
{
	public static function from(LogEntry ...$entries): self
    {
        return new self($entries);
    }

    public function add(LogEntry $entry): self
    {
        return self::from(...[...$this->items, $entry]);
    }
}
