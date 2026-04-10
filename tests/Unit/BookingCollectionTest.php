<?php

declare(strict_types=1);

use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\BookingCollection;
use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Event\Domain\ValueObjects\TicketId;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

function makeCollectionBooking(
    string $reference,
    BookingStatus $status,
    ?int $id = null,
    array $registration = [],
    array $attendeeMetadata = [],
): Booking {
    return new Booking(
        reference: BookingReference::fromString($reference),
        email: new Email('booking@example.test'),
        name: PersonName::from('Max', 'Mustermann'),
        priceSummary: PriceSummary::fromValues(
            bookingPrice: Price::from(1000, Currency::fromCode('EUR')),
            donationAmount: Price::from(0, Currency::fromCode('EUR')),
            discountAmount: Price::from(0, Currency::fromCode('EUR')),
        ),
        bookingTime: new DateTimeImmutable('2026-03-10 10:00:00'),
        status: $status,
        registration: new RegistrationData($registration),
        attendees: AttendeeCollection::from(...array_map(
            static function (array $metadata): Attendee {
                $ticketId = TicketId::from('ticket-1') ?? throw new RuntimeException('Invalid ticket id');

                return new Attendee(
                    ticketId: $ticketId,
                    ticketPrice: Price::from(1000, Currency::fromCode('EUR')),
                    name: null,
                    birthDate: null,
                    metadata: $metadata,
                );
            },
            $attendeeMetadata,
        )),
        gateway: 'manual',
        coupon: null,
        transactions: null,
        eventId: EventId::from(10),
        id: $id !== null ? BookingId::from($id) : null,
    );
}

test('booking collection exposes cancellable bookings only', function () {
    $collection = BookingCollection::from(
        makeCollectionBooking('BOOK-APPROVED', BookingStatus::APPROVED, 1),
        makeCollectionBooking('BOOK-PENDING', BookingStatus::PENDING, 2),
        makeCollectionBooking('BOOK-CANCELED', BookingStatus::CANCELED, 3),
    );

    $cancellable = $collection->cancellable()->toArray();

    expect($cancellable)->toHaveCount(2)
        ->and($cancellable[0]->reference->toString())->toBe('BOOK-APPROVED')
        ->and($cancellable[1]->reference->toString())->toBe('BOOK-PENDING');
});

test('booking collection collects registration and attendee metadata entries', function () {
    $collection = BookingCollection::from(
        makeCollectionBooking(
            'BOOK-ONE',
            BookingStatus::APPROVED,
            1,
            ['company' => 'Acme GmbH'],
            [['department' => 'Sales'], ['department' => 'Support']],
        ),
        makeCollectionBooking(
            'BOOK-TWO',
            BookingStatus::PENDING,
            2,
            ['cost_center' => 'CC-42'],
            [['shirt_size' => 'L']],
        ),
    );

    expect($collection->registrationEntries())->toBe([
        ['company' => 'Acme GmbH'],
        ['cost_center' => 'CC-42'],
    ])->and($collection->attendeeMetadataEntries())->toBe([
        ['department' => 'Sales'],
        ['department' => 'Support'],
        ['shirt_size' => 'L'],
    ]);
});
