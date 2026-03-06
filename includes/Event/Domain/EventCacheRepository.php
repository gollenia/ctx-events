<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Event\Application\DTOs\EventCacheSnapshot;

interface EventCacheRepository
{
    public function saveCache(EventCacheSnapshot $snapshot): void;
}
