<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\DTOs;

use Contexis\Events\Shared\Application\ValueObjects\Pagination;
use Contexis\Events\Shared\Domain\Abstract\DtoCollection;

final readonly class BookingListResponse extends DtoCollection
{
    public ?Pagination $pagination;
    public ?array $statusCounts;

    public function __construct(BookingListItem ...$items)
    {
        $this->items = $items;
    }

    public function withPagination(Pagination $pagination): self
    {
        return clone($this, ['pagination' => $pagination]);
    }

    public function withStatusCounts(array $statusCounts): self
    {
        return clone($this, ['statusCounts' => $statusCounts]);
    }

    public function hasStatusCounts(): bool
    {
        return $this->statusCounts !== null;
    }
}
