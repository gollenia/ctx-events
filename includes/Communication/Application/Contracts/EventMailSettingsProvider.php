<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\Contracts;

use Contexis\Events\Communication\Domain\ValueObjects\EventMailSettings;
use Contexis\Events\Event\Domain\ValueObjects\EventId;

interface EventMailSettingsProvider
{
    public function get(EventId $eventId): EventMailSettings;
}
