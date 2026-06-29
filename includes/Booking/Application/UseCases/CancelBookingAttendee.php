<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\DTOs\CancelBookingAttendeeRequest;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\Enums\BookingLogEvent;
use Contexis\Events\Booking\Domain\Enums\BookingLogLevel;
use Contexis\Events\Booking\Domain\Services\CalculateBookingPrice;
use Contexis\Events\Booking\Domain\ValueObjects\AttendeeId;
use Contexis\Events\Booking\Domain\ValueObjects\BookingNotesCollection;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntry;
use Contexis\Events\Communication\Application\BookingEmailWarnings;
use Contexis\Events\Communication\Application\Contracts\BookingEmailTrigger;
use Contexis\Events\Communication\Application\DTOs\BookingEmailResult;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\Contracts\CurrentActorProvider;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

final class CancelBookingAttendee
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private EventRepository $eventRepository,
        private CalculateBookingPrice $calculateBookingPrice,
        private Clock $clock,
        private CurrentActorProvider $currentActorProvider,
        private BookingEmailTrigger $bookingEmailTrigger,
    ) {
    }

    public function execute(CancelBookingAttendeeRequest $request): BookingEmailResult
    {
        $booking = $this->bookingRepository->findByReference($request->reference);

        if ($booking === null) {
            throw new \DomainException('Booking not found.');
        }

        $attendeeId = AttendeeId::from($request->attendeeId);
        if ($attendeeId === null) {
            throw new \DomainException('Invalid attendee.');
        }

        $attendee = $booking->attendees->getById($attendeeId);
        if ($attendee === null) {
            throw new \DomainException('Attendee not found.');
        }
        if (!$attendee->isActive()) {
            throw new \DomainException('Attendee is already cancelled.');
        }
        if ($booking->countAttendees() <= 1) {
            throw new \DomainException('The last active attendee cannot be cancelled. Cancel the booking instead.');
        }

        $event = $this->eventRepository->find($booking->eventId);
        if ($event === null || $event->tickets === null) {
            throw new \DomainException('Event not found.');
        }

        $cancelledAttendee = $attendee->cancel(
            Price::from($request->cancellationAmountCents, $attendee->ticketPrice->currency),
        );
        $attendees = $booking->attendees->replaceById($attendeeId, $cancelledAttendee);
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
                eventType: BookingLogEvent::AttendeeCancelled,
                level: BookingLogLevel::Info,
                actor: $this->currentActorProvider->current(),
                timestamp: $this->clock->now(),
                message: sprintf(
                    'Attendee %d cancelled with %d cents.',
                    $request->attendeeId,
                    $request->cancellationAmountCents,
                ),
            ));

        $this->bookingRepository->update($updatedBooking);

        $bookingId = $updatedBooking->id;
        if ($bookingId === null || !$request->sendMail) {
            return BookingEmailResult::empty();
        }

        $emailResult = $this->bookingEmailTrigger->trigger(
            EmailTrigger::TICKET_CANCELLED,
            $bookingId,
        );
        $logEntries = BookingEmailWarnings::appendToLogEntries(
            $updatedBooking->logEntries,
            $emailResult,
            $this->clock->now(),
        );

        if ($logEntries !== $updatedBooking->logEntries) {
            $this->bookingRepository->update($updatedBooking->withLogEntries($logEntries));
        }

        return $emailResult;
    }
}
