<?php
namespace Contexis\Events\Domain\Models;

enum BookingStatus: int { 
	case PENDING = 0;
	case APPROVED = 1;
	case REJECTED = 2;
	case CANCELED = 3;
	case EXPIRED = 4;
	case DELETED = 9;
};