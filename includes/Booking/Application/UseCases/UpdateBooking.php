<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\DTOs\UpdateBookingRequest;
use Contexis\Events\Booking\Domain\Services\CalculateBookingPrice;
use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\Enums\BookingLogEvent;
use Contexis\Events\Booking\Domain\Enums\BookingLogLevel;
use Contexis\Events\Booking\Domain\ValueObjects\LogEntry;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\Contracts\CurrentActorProvider;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

final class UpdateBooking
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private EventRepository $eventRepository,
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

        $attendees = AttendeeCollection::from(...array_map(
            static function (array $item) use ($currency): Attendee {
                $metadata = is_array($item['metadata'] ?? null) ? $item['metadata'] : [];
                $nameData = $item['name'] ?? null;
                $name     = is_array($nameData) && (($nameData['firstName'] ?? '') !== '' || ($nameData['lastName'] ?? '') !== '')
                    ? new PersonName((string) ($nameData['firstName'] ?? ''), (string) ($nameData['lastName'] ?? ''))
                    : null;

                return new Attendee(
                    ticketId:    TicketId::from($item['ticketId'] ?? $item['ticket_id'] ?? ''),
                    ticketPrice: Price::from(0, $currency),
                    name:        $name,
                    metadata:    $metadata,
                );
            },
            $request->attendees,
        ));

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
            $booking->notes,
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
