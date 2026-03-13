<?php
declare(strict_types=1);

use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Payment\Application\UseCases\SyncTransactionStatus;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Tests\Support\FakeBookingRepository;
use Tests\Support\FakeCurrentActorProvider;
use Tests\Support\FakeGatewayRepository;
use Tests\Support\FakePaymentGateway;
use Tests\Support\FakeTransactionRepository;

function makeSyncClock(): Clock
{
    $clock = Mockery::mock(Clock::class);
    $clock->allows('now')->andReturn(new DateTimeImmutable('2026-03-12 12:00:00'));

    return $clock;
}

function makeOnlineSyncBooking(): Booking
{
    return new Booking(
        reference: BookingReference::fromString('BOOK-2001'),
        email: new Email('online@example.test'),
        name: PersonName::from('Erika', 'Muster'),
        priceSummary: PriceSummary::fromValues(
            bookingPrice: Price::from(5000, Currency::fromCode('EUR')),
            donationAmount: Price::from(0, Currency::fromCode('EUR')),
            discountAmount: Price::from(0, Currency::fromCode('EUR'))
        ),
        bookingTime: new DateTimeImmutable('2026-03-10 10:00:00'),
        status: BookingStatus::PENDING,
        registration: new RegistrationData([
            'email' => 'online@example.test',
            'first_name' => 'Erika',
            'last_name' => 'Muster',
        ]),
        attendees: AttendeeCollection::empty(),
        gateway: 'mollie',
        coupon: null,
        transactions: null,
        eventId: EventId::from(2),
    );
}

test('paid online transaction approves the booking', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeOnlineSyncBooking());
    $transactionRepository = FakeTransactionRepository::withTransactions(
        Transaction::forPaymentService(
            bookingId: $bookingId,
            amount: Price::from(5000, Currency::fromCode('EUR')),
            externalId: 'tr_paid_1',
            checkoutUrl: \Uri\Rfc3986\Uri::parse('https://checkout.example.test/pay'),
            gateway: 'mollie',
            gatewayUrl: \Uri\Rfc3986\Uri::parse('https://gateway.example.test/pay/tr_paid_1'),
        )
    );

    $gateway = new FakePaymentGateway(
        id: 'mollie',
        verifyPaymentUsing: static fn(Transaction $transaction): Transaction => $transaction->complete(),
    );

    $useCase = new SyncTransactionStatus(
        transactionRepository: $transactionRepository,
        gatewayRepository: FakeGatewayRepository::withGateways([$gateway]),
        bookingRepository: $bookingRepository,
        clock: makeSyncClock(),
        currentActorProvider: new FakeCurrentActorProvider(),
    );

    $useCase->execute('tr_paid_1');

    expect($bookingRepository->find($bookingId)?->status)->toBe(BookingStatus::APPROVED)
        ->and($transactionRepository->findByExternalId('tr_paid_1')?->status)->toBe(TransactionStatus::PAID);
});

test('expired online transaction expires the booking', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeOnlineSyncBooking());
    $transactionRepository = FakeTransactionRepository::withTransactions(
        Transaction::forPaymentService(
            bookingId: $bookingId,
            amount: Price::from(5000, Currency::fromCode('EUR')),
            externalId: 'tr_expired_1',
            checkoutUrl: \Uri\Rfc3986\Uri::parse('https://checkout.example.test/pay'),
            gateway: 'mollie',
            gatewayUrl: \Uri\Rfc3986\Uri::parse('https://gateway.example.test/pay/tr_expired_1'),
        )
    );

    $gateway = new FakePaymentGateway(
        id: 'mollie',
        verifyPaymentUsing: static fn(Transaction $transaction): Transaction => $transaction->expire(),
    );

    $useCase = new SyncTransactionStatus(
        transactionRepository: $transactionRepository,
        gatewayRepository: FakeGatewayRepository::withGateways([$gateway]),
        bookingRepository: $bookingRepository,
        clock: makeSyncClock(),
        currentActorProvider: new FakeCurrentActorProvider(),
    );

    $useCase->execute('tr_expired_1');

    expect($bookingRepository->find($bookingId)?->status)->toBe(BookingStatus::EXPIRED)
        ->and($transactionRepository->findByExternalId('tr_expired_1')?->status)->toBe(TransactionStatus::EXPIRED);
});
