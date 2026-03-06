<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

use Contexis\Events\Event\Domain\ValueObjects\EventStatusCounts;

interface EventStatusRepository
{
    public function saveStatus(Event $event): void;

    public function getCountsByStatus(): EventStatusCounts;
}
