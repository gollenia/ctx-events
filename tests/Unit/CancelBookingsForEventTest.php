<?php

declare(strict_types=1);

use Contexis\Events\Booking\Application\Services\CancelBookingsForEvent;
use Contexis\Events\Booking\Application\Services\SyncOfflineTransactionForBookingAction;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Payment\Domain\ValueObjects\BankData;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Tests\Support\FakeBookingEmailTrigger;
use Tests\Support\FakeBookingRepository;
use Tests\Support\FakeCurrentActorProvider;
use Tests\Support\FakeTransactionRepository;

function makeEventBooking(
    string $reference,
    EventId $eventId,
    BookingStatus $status,
    string $gateway = 'offline',
): Booking {
    return new Booking(
        reference: BookingReference::fromString($reference),
        email: new Email('booking@example.test'),
        name: PersonName::from('Max', 'Mustermann'),
        priceSummary: PriceSummary::fromValues(
            bookingPrice: Price::from(5000, Currency::fromCode('EUR')),
            donationAmount: Price::from(0, Currency::fromCode('EUR')),
            discountAmount: Price::from(0, Currency::fromCode('EUR'))
        ),
        bookingTime: new DateTimeImmutable('2026-03-10 10:00:00'),
        status: $status,
        registration: new RegistrationData([
            'email' => 'booking@example.test',
            'first_name' => 'Max',
            'last_name' => 'Mustermann',
        ]),
        attendees: AttendeeCollection::from(),
        gateway: $gateway,
        coupon: null,
        transactions: null,
        eventId: $eventId,
    );
}

function makeEventBookingClock(): Clock
{
    $clock = Mockery::mock(Clock::class);
    $clock->allows('now')->andReturn(new DateTimeImmutable('2026-03-12 09:00:00'));

    return $clock;
}

function makeBookingTransaction(\Contexis\Events\Booking\Domain\ValueObjects\BookingId $bookingId): Transaction
{
    return Transaction::forBankTransfer(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        gateway: 'offline',
        bankData: BankData::fromValues('Example GmbH', 'AT611904300234573201', 'BKAUATWW', 'Example Bank'),
    )->complete();
}

test('cancel bookings for event cancels matching bookings and sends mails when enabled', function () {
    $eventId = EventId::from(55);
    $bookingRepository = FakeBookingRepository::empty();
    $approvedId = $bookingRepository->save(makeEventBooking('BOOK-APPROVED', $eventId, BookingStatus::APPROVED));
    $pendingId = $bookingRepository->save(makeEventBooking('BOOK-PENDING', $eventId, BookingStatus::PENDING));
    $alreadyCanceledId = $bookingRepository->save(makeEventBooking('BOOK-CANCELED', $eventId, BookingStatus::CANCELED));

    $transactionRepository = FakeTransactionRepository::withTransactions(
        makeBookingTransaction($approvedId),
        makeBookingTransaction($pendingId),
        makeBookingTransaction($alreadyCanceledId),
    );
    $bookingEmailTrigger = new FakeBookingEmailTrigger();

    $service = new CancelBookingsForEvent(
        bookingRepository: $bookingRepository,
        transactionSync: new SyncOfflineTransactionForBookingAction($transactionRepository),
        clock: makeEventBookingClock(),
        currentActorProvider: new FakeCurrentActorProvider(),
        bookingEmailTrigger: $bookingEmailTrigger,
    );

    $service->execute($eventId, true);

    expect($bookingRepository->find($approvedId)?->status)->toBe(BookingStatus::CANCELED)
        ->and($bookingRepository->find($pendingId)?->status)->toBe(BookingStatus::CANCELED)
        ->and($bookingRepository->find($alreadyCanceledId)?->status)->toBe(BookingStatus::CANCELED)
        ->and($transactionRepository->findLatestByBookingId($approvedId)?->status)->toBe(TransactionStatus::CANCELED)
        ->and($transactionRepository->findLatestByBookingId($pendingId)?->status)->toBe(TransactionStatus::CANCELED)
        ->and(count($bookingEmailTrigger->calls))->toBe(2)
        ->and($bookingEmailTrigger->calls[0]['trigger'])->toBe(EmailTrigger::BOOKING_CANCELLED)
        ->and($bookingEmailTrigger->calls[0]['cancellationReason'])->toBeNull()
        ->and($bookingEmailTrigger->calls[1]['trigger'])->toBe(EmailTrigger::BOOKING_CANCELLED);
});

test('cancel bookings for event skips email dispatch when disabled', function () {
    $eventId = EventId::from(56);
    $bookingRepository = FakeBookingRepository::empty();
    $approvedId = $bookingRepository->save(makeEventBooking('BOOK-NOMAIL', $eventId, BookingStatus::APPROVED));
    $transactionRepository = FakeTransactionRepository::withTransactions(makeBookingTransaction($approvedId));
    $bookingEmailTrigger = new FakeBookingEmailTrigger();

    $service = new CancelBookingsForEvent(
        bookingRepository: $bookingRepository,
        transactionSync: new SyncOfflineTransactionForBookingAction($transactionRepository),
        clock: makeEventBookingClock(),
        currentActorProvider: new FakeCurrentActorProvider(),
        bookingEmailTrigger: $bookingEmailTrigger,
    );

    $service->execute($eventId, false);

    expect($bookingRepository->find($approvedId)?->status)->toBe(BookingStatus::CANCELED)
        ->and($bookingEmailTrigger->lastCall())->toBeNull();
});

test('cancel bookings for event forwards cancellation reason to mail trigger', function () {
    $eventId = EventId::from(57);
    $bookingRepository = FakeBookingRepository::empty();
    $approvedId = $bookingRepository->save(makeEventBooking('BOOK-REASON', $eventId, BookingStatus::APPROVED));
    $transactionRepository = FakeTransactionRepository::withTransactions(makeBookingTransaction($approvedId));
    $bookingEmailTrigger = new FakeBookingEmailTrigger();

    $service = new CancelBookingsForEvent(
        bookingRepository: $bookingRepository,
        transactionSync: new SyncOfflineTransactionForBookingAction($transactionRepository),
        clock: makeEventBookingClock(),
        currentActorProvider: new FakeCurrentActorProvider(),
        bookingEmailTrigger: $bookingEmailTrigger,
    );

    $service->execute($eventId, true, 'Location unavailable');

    expect($bookingEmailTrigger->lastCall()['cancellationReason'] ?? null)->toBe('Location unavailable');
});
