<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\DTOs\UpdateBookingRequest;
use Contexis\Events\Booking\Domain\Services\CalculateBookingPrice;
use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\Enums\AttendeeStatus;
use Contexis\Events\Booking\Domain\Enums\BookingLogEvent;
use Contexis\Events\Booking\Domain\Enums\BookingLogLevel;
use Contexis\Events\Booking\Domain\ValueObjects\AttendeeId;
use Contexis\Events\Booking\Domain\ValueObjects\BookingNotesCollection;
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
        if ($event->tickets === null) {
            throw new \DomainException('Event has no tickets.');
        }

        $attendees = AttendeeCollection::from(...array_map(
            static function (array $item) use ($event): Attendee {
                $metadata = is_array($item['metadata'] ?? null) ? $item['metadata'] : [];
                $nameData = $item['name'] ?? null;
                $name     = is_array($nameData) && (($nameData['firstName'] ?? '') !== '' || ($nameData['lastName'] ?? '') !== '')
                    ? new PersonName((string) ($nameData['firstName'] ?? ''), (string) ($nameData['lastName'] ?? ''))
                    : null;
                $ticketId = TicketId::from($item['ticketId'] ?? $item['ticket_id'] ?? '');
                $ticket = $event->tickets->getTicketById($ticketId);
                $status = AttendeeStatus::tryFrom((string) ($item['status'] ?? AttendeeStatus::ACTIVE->value))
                    ?? AttendeeStatus::ACTIVE;
                $ticketPriceData = $item['ticketPrice'] ?? null;
                $ticketPriceCents = is_array($ticketPriceData)
                    ? (int) ($ticketPriceData['amountCents'] ?? $ticket->price->amountCents)
                    : (int) ($item['ticket_price'] ?? $ticket->price->amountCents);

                return new Attendee(
                    ticketId:    $ticketId,
                    ticketPrice: Price::from($ticketPriceCents, $ticket->price->currency),
                    name:        $name,
                    metadata:    $metadata,
                    status:      $status,
                    id:          isset($item['id']) ? AttendeeId::from((int) $item['id']) : null,
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
