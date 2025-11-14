<?php

namespace Contexis\Events\Booking\Domain;

enum BookingDenyReason: string
{
    case DISABLED   = 'disabled';
    case NO_CAPACITY = 'no_capacity';
    case NOT_STARTED = 'not_started';
    case ENDED      = 'ended';
    case SOLD_OUT    = 'sold_out';
}
