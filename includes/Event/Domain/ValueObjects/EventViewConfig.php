<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain\ValueObjects;

final class EventViewConfig
{
    public function __construct(
        public readonly int $showFreeSpacesThreshold = 0,
		public readonly bool $showFreeSpaces = true
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            showFreeSpacesThreshold: $data['showFreeSpacesThreshold'] ?? 0,
            showFreeSpaces: $data['showFreeSpaces'] ?? true
        );
    }

    public function showFreeSpaces(?int $freeSpaces): bool
    {
        if (!$this->showFreeSpaces) {
			return false;
		}

		return $freeSpaces <= $this->showFreeSpacesThreshold;
    }
}
