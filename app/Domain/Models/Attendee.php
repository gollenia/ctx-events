<?php

namespace Contexis\Events\Domain\Models;

use Contexis\Events\Domain\ValueObjects\BookingPolicy;

final class Attendee {
	public function __construct(
		public readonly string $ticket_id,
		public readonly string $booking_id,
		public readonly string $first_name,
		public readonly string $last_name,
		public readonly string $email,
		public readonly array $metadata = []
	) {}
}