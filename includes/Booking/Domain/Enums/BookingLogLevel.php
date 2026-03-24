<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\Enums;

enum BookingLogLevel: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Error = 'error';
}
