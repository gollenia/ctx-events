<?php

namespace Contexis\Events\Domain\ValueObjects;

enum BookingDenyReason: string {
	case DISABLED   = 'disabled';
	case NO_CAPACITY = 'no_capacity';   
	case NOT_STARTED = 'not_started';
	case ENDED      = 'ended';
	case SOLD_OUT    = 'sold_out';      
}

final class BookingDecision {
	private function __construct(
		public readonly bool $allowed,
		public readonly ?BookingDenyReason $reason = null
	) {}

	public static function allow(): self { 
		return new self(true); 
	}

	public static function deny(BookingDenyReason $r): self { 
		return new self(false, $r); 
	}

	public function message(): string {
		return match ($this->reason) {
			BookingDenyReason::DISABLED   => 'Booking is disabled for this event.',
			BookingDenyReason::NO_CAPACITY => 'No capacity available.',
			BookingDenyReason::NOT_STARTED => 'Booking period has not started yet.',
			BookingDenyReason::ENDED      => 'Booking period has ended.',
			BookingDenyReason::SOLD_OUT   => 'Tickets are sold out.',
			default => '',
		};
	}
}