<?php

declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Communication\Application\Contracts\EventMailTemplateOverrideStore;
use Contexis\Events\Event\Domain\ValueObjects\EventId;

final class FakeEventMailTemplateOverrideStore implements EventMailTemplateOverrideStore
{
    /**
     * @param array<int, array<string, array<string, mixed>>> $overridesByEventId
     */
    public function __construct(
        private array $overridesByEventId = [],
    ) {
    }

    public function eventMailTemplateOverrides(EventId $eventId): array
    {
        return $this->overridesByEventId[$eventId->toInt()] ?? [];
    }
}
