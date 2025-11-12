<?php

namespace Contexis\Events\Domain\Collections;

use Contexis\Events\Domain\Contracts\WithPagination;
use Contexis\Events\Domain\Models\Event;

final class EventCollection extends AbstractCollection implements WithPagination
{
	private int $totalItems;
	private int $currentPage;
	private int $perPage;

    public function __construct(Event ...$events)
    {
        $this->items = $events;
    }

	public function withPagination(int $totalItems, int $currentPage, int $perPage): self
	{
		$clone = clone $this;
        $clone->totalItems = $totalItems;
		$clone->currentPage = $currentPage;
        $clone->perPage = $perPage;
        return $clone;
	}

	public function getTotalItems(): int
	{
		return $this->totalItems;
	}

	public function getTotalPages(): int
	{
		return (int) ceil($this->totalItems / $this->perPage);
	}

	public function getCurrentPage(): int
	{
		return $this->currentPage;
	}

	public function getPerPage(): int
	{
		return $this->perPage;
	}

    public function uniqueLocationIds(): array
    {
        $ids = array_map(
            fn(Event $event) => $event->locationId,
            $this->items
        );

        return array_values(array_unique(array_filter($ids)));
    }
}
