<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain\ValueObjects;

/**
 * Shared content filters for individual events, recurring series and
 * occurrence projections.
 */
final readonly class EventFilters
{
    /**
     * @param list<int> $categories
     * @param list<int> $tags
     */
    public function __construct(
        public array $categories = [],
        public array $tags = [],
        public ?int $locationId = null,
        public ?int $personId = null,
        public ?string $search = null,
    ) {
    }

    public static function empty(): self
    {
        return new self();
    }
}
