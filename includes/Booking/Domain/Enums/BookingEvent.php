<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Domain\Enums;

enum BookingEvent: string
{
	case Created = 'created';
	case Updated = 'updated';
	case Deleted = 'deleted';
	case Approved = 'approved';
	case Rejected = 'rejected';
	case Cancelled = 'cancelled';
	case Restored = 'restored';
}