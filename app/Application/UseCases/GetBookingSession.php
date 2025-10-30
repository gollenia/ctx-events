<?php

namespace Contexis\Events\Application\UseCases;

final class GetBookingSession {
	public function __construct(
		private readonly int $bookingSessionId
	) {}

	public function execute(): BookingSessionDto {
			// Implementation logic to retrieve booking session by ID
		return new BookingSessionDto($this->bookingSessionId);
	}
}