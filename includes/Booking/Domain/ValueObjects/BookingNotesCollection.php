<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\ValueObjects;

use Contexis\Events\Booking\Domain\ValueObjects\BookingNote;
use Contexis\Events\Shared\Domain\Abstract\Collection;

final readonly class BookingNotesCollection extends Collection
{
	 public function __construct(
        BookingNote ...$entries
    ) {
        $this->items = $entries;
    }
}
