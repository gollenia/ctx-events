<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain\Enums;

enum OccurrenceExceptionType: string
{
    case Cancelled = 'cancelled';
    case Detached = 'detached';
}
