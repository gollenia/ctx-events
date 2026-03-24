<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Application\Contracts;

use Contexis\Events\Event\Application\DTOs\EventCalendarFile;
use Contexis\Events\Event\Domain\Event;

interface EventCalendarExporter
{
    public function export(Event $event): EventCalendarFile;
}
