<?php

namespace Contexis\Events\Application\DTO;

final class PagedList
{
	public function __construct(
		public readonly array $items,
		public readonly int $totalItems,
		public readonly int $totalPages,
		public readonly int $currentPage,
		public readonly int $perPage
	) {
	}
}