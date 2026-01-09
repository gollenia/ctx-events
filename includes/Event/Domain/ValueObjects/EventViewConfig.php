<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain\ValueObjects;

final class EventViewConfig
{
    public function __construct(
        public readonly int $showFreeSpacesThreshold = 0
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            showFreeSpacesThreshold: $data['show_free_spaces_threshold'] ?? 0
        );
    }

    public function showFreeSpaces(): bool
    {
        return $this->showFreeSpacesThreshold > 0;
    }
}
