<?php
declare(strict_types=1);

use Contexis\Events\Booking\Application\DTOs\BookingActionRequest;
use Contexis\Events\Booking\Application\Services\SyncOfflineTransactionForBookingAction;
use Contexis\Events\Booking\Application\UseCases\ApproveBooking;
use Contexis\Events\Booking\Application\UseCases\CancelBooking;
use Contexis\Events\Booking\Application\UseCases\DenyBooking;
use Contexis\Events\Booking\Application\UseCases\RestoreBooking;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Payment\Domain\ValueObjects\BankData;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Tests\Support\FakeBookingRepository;
use Tests\Support\FakeCurrentActorProvider;
use Tests\Support\FakeTransactionRepository;

function makeOfflineBooking(BookingStatus $status = BookingStatus::PENDING): Booking
{
    return new Booking(
        reference: BookingReference::fromString('BOOK-1001'),
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
        gateway: 'offline',
        coupon: null,
        transactions: null,
        eventId: EventId::from(1),
    );
}

function makeOfflineTransaction(\Contexis\Events\Booking\Domain\ValueObjects\BookingId $bookingId): Transaction
{
    return Transaction::forBankTransfer(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        gateway: 'offline',
        bankData: BankData::fromValues('Example GmbH', 'AT611904300234573201', 'BKAUATWW', 'Example Bank'),
    );
}

function makeActionClock(): Clock
{
    $clock = Mockery::mock(Clock::class);
    $clock->allows('now')->andReturn(new DateTimeImmutable('2026-03-12 09:00:00'));

    return $clock;
}

test('approving an offline booking marks its transaction as paid', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeOfflineBooking());
    $transactionRepository = FakeTransactionRepository::withTransactions(makeOfflineTransaction($bookingId));

    $useCase = new ApproveBooking(
        repository: $bookingRepository,
        transactionSync: new SyncOfflineTransactionForBookingAction($transactionRepository),
        clock: makeActionClock(),
        currentActorProvider: new FakeCurrentActorProvider(),
    );

    $useCase->execute(new BookingActionRequest('BOOK-1001', true));

    expect($transactionRepository->findLatestByBookingId($bookingId)?->status)
        ->toBe(TransactionStatus::PAID);
});

test('denying an offline booking leaves its transaction unchanged', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeOfflineBooking());
    $transactionRepository = FakeTransactionRepository::withTransactions(makeOfflineTransaction($bookingId));

    $useCase = new DenyBooking(
        repository: $bookingRepository,
        clock: makeActionClock(),
        currentActorProvider: new FakeCurrentActorProvider(),
    );

    $useCase->execute(new BookingActionRequest('BOOK-1001', true));

    expect($transactionRepository->findLatestByBookingId($bookingId)?->status)
        ->toBe(TransactionStatus::PENDING);
});

test('canceling an offline booking cancels its transaction', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeOfflineBooking(BookingStatus::APPROVED));
    $transactionRepository = FakeTransactionRepository::withTransactions(makeOfflineTransaction($bookingId)->complete());

    $useCase = new CancelBooking(
        repository: $bookingRepository,
        transactionSync: new SyncOfflineTransactionForBookingAction($transactionRepository),
        clock: makeActionClock(),
        currentActorProvider: new FakeCurrentActorProvider(),
    );

    $useCase->execute(new BookingActionRequest('BOOK-1001', true));

    expect($transactionRepository->findLatestByBookingId($bookingId)?->status)
        ->toBe(TransactionStatus::CANCELED);
});

test('restoring an offline booking resets its transaction to pending', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeOfflineBooking(BookingStatus::CANCELED));
    $transactionRepository = FakeTransactionRepository::withTransactions(makeOfflineTransaction($bookingId)->expire());

    $useCase = new RestoreBooking(
        repository: $bookingRepository,
        transactionSync: new SyncOfflineTransactionForBookingAction($transactionRepository),
        clock: makeActionClock(),
        currentActorProvider: new FakeCurrentActorProvider(),
    );

    $useCase->execute(new BookingActionRequest('BOOK-1001', true));

    expect($transactionRepository->findLatestByBookingId($bookingId)?->status)
        ->toBe(TransactionStatus::PENDING);
});
