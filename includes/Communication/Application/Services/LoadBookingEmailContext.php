<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\Services;

use Contexis\Events\Booking\Domain\AttendeeRepository;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Communication\Application\DTOs\TriggeredEmailContext;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Location\Domain\LocationRepository;
use Contexis\Events\Person\Domain\PersonRepository;
use Contexis\Events\Payment\Domain\TransactionRepository;

final readonly class LoadBookingEmailContext
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private EventRepository $eventRepository,
        private AttendeeRepository $attendeeRepository,
        private TransactionRepository $transactionRepository,
        private LocationRepository $locationRepository,
        private PersonRepository $personRepository,
    ) {
    }

    public function load(BookingId $bookingId, ?string $cancellationReason = null): ?TriggeredEmailContext
    {
        $booking = $this->bookingRepository->find($bookingId);

        if ($booking === null) {
            return null;
        }

        $event = $this->eventRepository->find($booking->eventId);

        if ($event === null) {
            return null;
        }

        $locationName = $event->locationId !== null
            ? $this->locationRepository->find($event->locationId)?->name
            : null;
        $speaker = $event->personId !== null
            ? $this->personRepository->find($event->personId)
            : null;
        $speakerName = $speaker
            ? implode(' ', array_filter([
                $speaker->honorificPrefix,
                $speaker->givenName,
                $speaker->familyName,
                $speaker->honorificSuffix,
            ], static fn (?string $part): bool => $part !== null && trim($part) !== ''))
            : null;

        return new TriggeredEmailContext(
            booking: $booking,
            event: $event,
            attendees: $this->attendeeRepository->findByBookingId($bookingId),
            transactions: $this->transactionRepository->findByBookingId($bookingId),
            eventLocationName: $locationName,
            eventSpeakerName: $speakerName !== '' ? $speakerName : null,
            cancellationReason: $cancellationReason,
        );
    }
}
