<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\DTOs\AddBookingAttendeeRequest;
use Contexis\Events\Booking\Application\Services\AttendeeFactory;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\Enums\BookingLogEvent;
use Contexis\Events\Booking\Domain\Enums\BookingLogLevel;
use Contexis\Events\Booking\Domain\Services\CalculateBookingPrice;
use Contexis\Events\Booking\Domain\ValueObjects\BookingNotesCollection;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntry;
use Contexis\Events\Event\Application\Service\PrepareBookingTicketLimits;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\TicketCollection;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\Contracts\CurrentActorProvider;

final class AddBookingAttendee
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private EventRepository $eventRepository,
        private AttendeeFactory $attendeeFactory,
        private PrepareBookingTicketLimits $prepareBookingTicketLimits,
        private CalculateBookingPrice $calculateBookingPrice,
        private Clock $clock,
        private CurrentActorProvider $currentActorProvider,
    ) {
    }

    public function execute(AddBookingAttendeeRequest $request): void
    {
        $booking = $this->bookingRepository->findByReference($request->reference);

        if ($booking === null) {
            throw new \DomainException('Booking not found.');
        }

        $event = $this->eventRepository->find($booking->eventId);

        if ($event === null || $event->tickets === null) {
            throw new \DomainException('Event not found.');
        }

        $ticketId = TicketId::from((string) ($request->attendee['ticketId'] ?? $request->attendee['ticket_id'] ?? ''));
        if ($ticketId === null) {
            throw new \DomainException('Invalid ticket.');
        }

        $ticketBookingsMap = $this->bookingRepository->getTicketBookingsForEvent($event->id);
        $tickets = $this->getBookableTickets($event->tickets);

        $ticketResponses = $this->prepareBookingTicketLimits->map(
            $tickets,
            $ticketBookingsMap,
            $event->overallCapacity,
        );

        $selectedTicket = null;
        foreach ($ticketResponses as $ticketResponse) {
            if ($ticketResponse->id === $ticketId->toString()) {
                $selectedTicket = $ticketResponse;
                break;
            }
        }

        if ($selectedTicket === null || ($selectedTicket->bookingLimit !== null && $selectedTicket->bookingLimit < 1)) {
            throw new \DomainException('No seats available for the selected ticket.');
        }

        $newAttendee = $this->attendeeFactory->oneFromAdminPayload($request->attendee, $event->tickets);
        $attendees = AttendeeCollection::from(
            ...[
                ...iterator_to_array($booking->attendees),
                $newAttendee,
            ],
        );

        $priceSummary = $this->calculateBookingPrice->perform(
            availableTickets: $event->tickets,
            coupon: $booking->coupon,
            attendees: $attendees,
            donation: $booking->priceSummary->donationAmount,
            currency: $booking->priceSummary->finalPrice->currency,
        );

        $updatedBooking = $booking
            ->update(
                $booking->registration,
                $attendees,
                $booking->gateway ?? '',
                $booking->notes ?? BookingNotesCollection::empty(),
                $priceSummary,
            )
            ->appendLogEntry(new LogEntry(
                eventType: BookingLogEvent::Updated,
                level: BookingLogLevel::Info,
                actor: $this->currentActorProvider->current(),
                timestamp: $this->clock->now(),
                message: sprintf('Attendee added for ticket %s.', $ticketId->toString()),
            ));

        $this->bookingRepository->update($updatedBooking);
    }

    private function getBookableTickets(TicketCollection $tickets): TicketCollection
    {
        return $tickets
            ->getEnabledTickets()
            ->getValidTicketsForDate($this->clock->now());
    }
}
