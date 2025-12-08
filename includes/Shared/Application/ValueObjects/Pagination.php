<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Application\ValueObjects;

final class Pagination
{
    public function __construct(
        public readonly int $currentPage,
        public readonly int $perPage,
        public readonly int $totalItems
    ) {
    }

    public static function empty(): self
    {
        return new self(
            currentPage: 1,
            perPage: 0,
            totalItems: 0
        );
    }

    public static function of(
        int $totalItems,
        int $currentPage,
        int $perPage
    ): self {
        return new self(
            currentPage: $currentPage,
            perPage: $perPage,
            totalItems: $totalItems
        );
    }

    public function totalPages(): int
    {
        return (int) ceil($this->totalItems / $this->perPage);
    }
}
