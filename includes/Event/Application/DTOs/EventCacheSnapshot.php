<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Application\DTOs;

final readonly class EventCacheSnapshot
{
    public function __construct(
        public int $eventId,
        public ?int $minPriceAmountCents,
        public ?int $maxPriceAmountCents,
    ) {
    }
}
