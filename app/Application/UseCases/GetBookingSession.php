<?php

namespace Contexis\Events\Application\UseCases;

use Contexis\Events\Application\DTO\BookingSessionDto;
use Contexis\Events\Domain\Repositories\ {
	BookingRepository,
	AttendeeRepository,
	EventRepository
};

use Contexis\Events\Domain\ {
	Booking,
	Event,
	Attendee
};

final class GetBookingSession {

	public function __construct(
		private BookingRepository $bookingRepository,
		private EventRepository $eventRepository,
		private AttendeeRepository $attendeeRepository
	) {
	}

	public function execute(int $event_id): BookingSessionDto {

		$event = $this->eventRepository->by_id($event_id);
		$bookings = $this->bookingRepository->find_by_event_id($event_id);
		$attendees = $this->attendeeRepository->find($bookingSession->get_attendees());

		return new BookingSessionDto(
			$bookingSession->get_id(),
			$event->get_id(),
			$bookingSession->get_user_id(),
			$bookingSession->get_attendees()
		);
	}
}