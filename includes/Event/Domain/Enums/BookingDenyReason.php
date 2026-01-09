<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain\Enums;

enum BookingDenyReason: string
{
    case DISABLED   = 'disabled';
    case NO_CAPACITY = 'no_capacity';
    case NOT_STARTED = 'not_started';
    case ENDED      = 'ended';
    case SOLD_OUT    = 'sold_out';
	case FORM_ERROR = 'form_error';
}
