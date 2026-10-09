<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain\Enums;

enum OccurrenceState: string
{
    case Virtual = 'virtual';
    case Detached = 'detached';
}
