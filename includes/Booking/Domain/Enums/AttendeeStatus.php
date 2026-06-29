<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\Enums;

enum AttendeeStatus: string
{
	case ACTIVE = 'active';
	case CANCELLED = 'cancelled';
	case CHECKED_IN = 'checked_in';
}
