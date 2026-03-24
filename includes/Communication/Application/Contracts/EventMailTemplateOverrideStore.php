<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\Contracts;

use Contexis\Events\Event\Domain\ValueObjects\EventId;

interface EventMailTemplateOverrideStore
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function eventMailTemplateOverrides(EventId $eventId): array;
}
