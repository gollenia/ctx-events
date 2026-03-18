<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\DTOs;

use Contexis\Events\Shared\Application\ValueObjects\Pagination;
use Contexis\Events\Shared\Domain\Abstract\DtoCollection;

final readonly class BookingListResponse extends DtoCollection
{
    public static function from(BookingListItem ...$items): self
    {
        return new self($items);
    }


    
}
