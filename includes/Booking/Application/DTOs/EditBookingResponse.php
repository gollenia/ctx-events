<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\DTOs;

use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Domain\ValueObjects\BookingNote;
use Contexis\Events\Booking\Domain\ValueObjects\BookingNotesCollection;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Application\DTOs\TicketResponseCollection;
use Contexis\Events\Event\Domain\Event;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Form\Domain\Form;
use Contexis\Events\Payment\Domain\GatewayCollection;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;

final readonly class EditBookingResponse
{
    public function __construct(
        public Booking $booking,
        public EventId $eventId,
        public string $eventTitle,
		public Form $registrationForm,
		public Form $attendeeForm,
		public GatewayCollection $availableGateways,
        public BookingNotesCollection $notes,
        public TicketResponseCollection $availableTickets,
    ) {
    }

	public static function from(
		Booking $booking,
		Event $event,
		Form $registrationForm,
		Form $attendeeForm,
		GatewayCollection $availableGateways,
		BookingNotesCollection $notes,
		TicketResponseCollection $availableTickets,
	): self {
		return new self(
			booking: $booking,
			eventId: $booking->eventId,
			eventTitle: $event->name,
			registrationForm: $registrationForm,
			attendeeForm: $attendeeForm,
			availableGateways: $availableGateways,
			notes: $notes,
			availableTickets: $availableTickets,
		);
	}
}