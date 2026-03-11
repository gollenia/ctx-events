<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Application\UseCases;

use Contexis\Events\Booking\Application\DTOs\UpdateBookingRequest;
use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\BookingRepository;
use Contexis\Events\Booking\Domain\ValueObjects\BookingNote;
use Contexis\Events\Booking\Domain\ValueObjects\BookingNotesCollection;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

final class UpdateBooking
{
    public function __construct(private BookingRepository $bookingRepository) {}

    public function execute(UpdateBookingRequest $request): void
    {
        $booking = $this->bookingRepository->findByReference($request->uuid);

        if ($booking === null) {
            throw new \DomainException('Booking not found.');
        }

        $currency = $booking->priceSummary->finalPrice->currency;

        $notes = new BookingNotesCollection(...array_map(
            static fn(array $item): BookingNote => BookingNote::fromArray($item),
            $request->notes,
        ));

        $attendees = new AttendeeCollection(...array_map(
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

        $updated = $booking->update(
            new RegistrationData($request->registration),
            $attendees,
        	 $request->gateway,
             $notes,
            $booking->priceSummary->withDonation(
                Price::from($request->donationCents, $currency),
            )
        );

        $this->bookingRepository->update($updated);
    }
}
