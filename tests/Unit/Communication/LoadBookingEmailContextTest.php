<?php

declare(strict_types=1);

use Contexis\Events\Booking\Domain\Attendee;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Communication\Application\Services\LoadBookingEmailContext;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Tests\Support\FakeAttendeeRepository;
use Tests\Support\FakeBookingRepository;
use Tests\Support\FakeEventFactory;
use Tests\Support\FakeEventRepository;
use Tests\Support\FakeTransactionRepository;

function makeTriggeredEmailBooking(EventId $eventId): Booking
{
    return new Booking(
        reference: BookingReference::fromString('BOOK-MAIL-1001'),
        email: new Email('booking@example.test'),
        name: PersonName::from('Max', 'Mustermann'),
        priceSummary: PriceSummary::fromValues(
            bookingPrice: Price::from(5000, Currency::fromCode('EUR')),
            donationAmount: Price::from(0, Currency::fromCode('EUR')),
            discountAmount: Price::from(0, Currency::fromCode('EUR'))
        ),
        bookingTime: new DateTimeImmutable('2026-03-10 10:00:00'),
        status: BookingStatus::PENDING,
        registration: new RegistrationData([
            'email' => 'booking@example.test',
            'first_name' => 'Max',
            'last_name' => 'Mustermann',
        ]),
        attendees: AttendeeCollection::empty(),
        gateway: 'offline',
        coupon: null,
        transactions: null,
        eventId: $eventId,
    );
}

test('loads booking event attendees and transactions for a booking id', function () {
    $event = FakeEventFactory::create(200);
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeTriggeredEmailBooking($event->id));

    $attendees = AttendeeCollection::from(new Attendee(
        ticketId: $event->tickets->toArray()[0]->id,
        ticketPrice: Price::from(5000, Currency::fromCode('EUR')),
        name: PersonName::from('Erika', 'Muster'),
        birthDate: null,
        metadata: [],
    ));
    $transactions = FakeTransactionRepository::withTransactions(Transaction::forBankTransfer(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        gateway: 'offline',
    ));
    $attendeeRepository = FakeAttendeeRepository::withBookingAttendees($bookingId, $attendees);

    $loader = new LoadBookingEmailContext(
        bookingRepository: $bookingRepository,
        eventRepository: FakeEventRepository::one($event),
        attendeeRepository: $attendeeRepository,
        transactionRepository: $transactions,
    );

    $context = $loader->load($bookingId);

    expect($context)->not->toBeNull();
    expect($context?->booking->id)->toEqual($bookingId);
    expect($context?->event->id)->toEqual($event->id);
    expect($context?->attendees)->toEqual($attendees);
    expect($context?->transactions->count())->toBe(1);
    expect($bookingRepository->lastFindArg)->toEqual($bookingId);
    expect($attendeeRepository->lastFindArg)->toEqual($bookingId);
    expect($transactions->lastFindArg)->toEqual($bookingId);
});

test('returns null when booking does not exist', function () {
    $bookingId = \Contexis\Events\Booking\Domain\ValueObjects\BookingId::from(999)
        ?? throw new RuntimeException('Invalid booking id');

    $loader = new LoadBookingEmailContext(
        bookingRepository: FakeBookingRepository::empty(),
        eventRepository: FakeEventRepository::empty(),
        attendeeRepository: FakeAttendeeRepository::empty(),
        transactionRepository: FakeTransactionRepository::empty(),
    );

    expect($loader->load($bookingId))->toBeNull();
});

test('returns null when the booking event does not exist', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $eventId = EventId::from(404);
    $bookingId = $bookingRepository->save(makeTriggeredEmailBooking($eventId));

    $loader = new LoadBookingEmailContext(
        bookingRepository: $bookingRepository,
        eventRepository: FakeEventRepository::empty(),
        attendeeRepository: FakeAttendeeRepository::empty(),
        transactionRepository: FakeTransactionRepository::empty(),
    );

    expect($loader->load($bookingId))->toBeNull();
});
