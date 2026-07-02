<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\DTOs\UpdateBookingRequest;
use Contexis\Events\Booking\Application\Services\AttendeeFactory;
use Contexis\Events\Booking\Domain\Services\CalculateBookingPrice;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\Enums\BookingLogEvent;
use Contexis\Events\Booking\Domain\Enums\BookingLogLevel;
use Contexis\Events\Booking\Domain\ValueObjects\BookingNotesCollection;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntry;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\Contracts\CurrentActorProvider;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

final class UpdateBooking
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private EventRepository $eventRepository,
        private AttendeeFactory $attendeeFactory,
        private CalculateBookingPrice $calculateBookingPrice,
        private Clock $clock,
        private CurrentActorProvider $currentActorProvider,
    ) {}

    public function execute(UpdateBookingRequest $request): void
    {
        $booking = $this->bookingRepository->findByReference($request->uuid);

        if ($booking === null) {
            throw new \DomainException('Booking not found.');
        }

        $currency = $booking->priceSummary->finalPrice->currency;
        $event = $this->eventRepository->find($booking->eventId);

        if ($event === null) {
            throw new \DomainException('Event not found.');
        }
        if ($event->tickets === null) {
            throw new \DomainException('Event has no tickets.');
        }

        $attendees = $this->attendeeFactory->fromAdminPayload($request->attendees, $event->tickets);

        $priceSummary = $this->calculateBookingPrice->perform(
            availableTickets: $event->tickets,
            coupon: $booking->coupon,
            attendees: $attendees,
            donation: Price::from($request->donationCents, $currency),
            currency: $currency,
        );

        $updated = $booking->update(
            new RegistrationData($request->registration),
            $attendees,
            $request->gateway ?? '',
            $booking->notes ?? BookingNotesCollection::empty(),
            $priceSummary,
        )->appendLogEntry(new LogEntry(
            eventType: BookingLogEvent::Updated,
            level: BookingLogLevel::Info,
            actor: $this->currentActorProvider->current(),
            timestamp: $this->clock->now(),
        ));

        $this->bookingRepository->update($updated);
    }
}
