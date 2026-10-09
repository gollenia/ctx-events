<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain\Enums;

enum RecurrenceWeekday: string
{
    case Monday = 'monday';
    case Tuesday = 'tuesday';
    case Wednesday = 'wednesday';
    case Thursday = 'thursday';
    case Friday = 'friday';
    case Saturday = 'saturday';
    case Sunday = 'sunday';
}
