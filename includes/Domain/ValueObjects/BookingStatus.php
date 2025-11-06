<?php

namespace Contexis\Events\Domain\ValueObjects;

enum BookingStatus: int
{
    case PENDING = 1;
    case CONFIRMED = 2;
    case CANCELLED = 3;
    case EXPIRED = 4;
    case DELETED = 9;
}
