<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use Contexis\Events\Booking\Domain\ValueObjects\BookingNote;
use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class BookingNotesCollection extends Collection
{
	public static function from(BookingNote ...$entries): self
    {
        return new self($entries);
    }

    public function add(BookingNote $entry): self
    {
        $items = [...$this->items, $entry];

        return self::from(...$items);
    }
}
