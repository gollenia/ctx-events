<?php

declare(strict_types=1);

use Contexis\Events\Booking\Application\UseCases\ResolveBookingPaymentLink;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Payment\Domain\Enums\TransactionStatus;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Tests\Support\FakeBookingRepository;
use Tests\Support\FakeGatewayRepository;
use Tests\Support\FakePaymentGateway;
use Tests\Support\FakeTransactionRepository;
use Uri\Rfc3986\Uri;

function makeResolvePaymentLinkClock(string $now = '2026-03-18 12:00:00'): Clock
{
    $clock = Mockery::mock(Clock::class);
    $clock->allows('now')->andReturn(new DateTimeImmutable($now));

    return $clock;
}

function makePaidOnlineBooking(): Booking
{
    return new Booking(
        reference: BookingReference::fromString('BOOK-3001'),
        email: new Email('link@example.test'),
        name: PersonName::from('Erika', 'Muster'),
        priceSummary: PriceSummary::fromValues(
            bookingPrice: Price::from(5000, Currency::fromCode('EUR')),
            donationAmount: Price::from(0, Currency::fromCode('EUR')),
            discountAmount: Price::from(0, Currency::fromCode('EUR'))
        ),
        bookingTime: new DateTimeImmutable('2026-03-18 10:00:00'),
        status: BookingStatus::PENDING,
        registration: new RegistrationData([
            'email' => 'link@example.test',
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

test('reuses latest pending online transaction when it is still valid', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $booking = makePaidOnlineBooking();
    $bookingId = $bookingRepository->save($booking);
    $booking = $booking->withId($bookingId);

    $existing = Transaction::forPaymentService(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        externalId: 'tr_reuse_me',
        checkoutUrl: Uri::parse('https://checkout.example.test/reuse'),
        gateway: 'mollie',
        gatewayUrl: Uri::parse('https://gateway.example.test/pay/tr_reuse_me'),
        expiresAt: new DateTimeImmutable('2026-03-20 12:00:00'),
    )->withCreatedAt(new DateTimeImmutable('2026-03-18 11:00:00'));

    $transactionRepository = FakeTransactionRepository::withTransactions($existing);
    $gateway = new FakePaymentGateway(
        id: 'mollie',
        verifyPaymentUsing: static fn(Transaction $transaction): Transaction => $transaction,
        initiatePaymentUsing: static fn() => throw new RuntimeException('Should not create a new transaction'),
    );

    $useCase = new ResolveBookingPaymentLink(
        bookingRepository: $bookingRepository,
        transactionRepository: $transactionRepository,
        gatewayRepository: FakeGatewayRepository::withGateways([$gateway]),
        clock: makeResolvePaymentLinkClock(),
    );

    $resolved = $useCase->execute($booking->reference->toString());

    expect($resolved->externalId)->toBe('tr_reuse_me')
        ->and($resolved->checkoutUrl?->toString())->toBe('https://checkout.example.test/reuse');
});

test('creates a new transaction when the latest online payment is expired', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $booking = makePaidOnlineBooking();
    $bookingId = $bookingRepository->save($booking);
    $booking = $booking->withId($bookingId);

    $expired = Transaction::forPaymentService(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        externalId: 'tr_old_expired',
        checkoutUrl: Uri::parse('https://checkout.example.test/expired'),
        gateway: 'mollie',
        gatewayUrl: Uri::parse('https://gateway.example.test/pay/tr_old_expired'),
        expiresAt: new DateTimeImmutable('2026-03-17 12:00:00'),
    )->withCreatedAt(new DateTimeImmutable('2026-03-17 10:00:00'));

    $transactionRepository = FakeTransactionRepository::withTransactions($expired);
    $newTransaction = Transaction::forPaymentService(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        externalId: 'tr_new_payment',
        checkoutUrl: Uri::parse('https://checkout.example.test/new'),
        gateway: 'mollie',
        gatewayUrl: Uri::parse('https://gateway.example.test/pay/tr_new_payment'),
        expiresAt: new DateTimeImmutable('2026-03-21 12:00:00'),
    )->withCreatedAt(new DateTimeImmutable('2026-03-18 12:00:00'));

    $gateway = new FakePaymentGateway(
        id: 'mollie',
        verifyPaymentUsing: static fn(Transaction $transaction): Transaction => $transaction,
        initiatePaymentUsing: static fn(Booking $resolvedBooking): Transaction => $newTransaction,
    );

    $useCase = new ResolveBookingPaymentLink(
        bookingRepository: $bookingRepository,
        transactionRepository: $transactionRepository,
        gatewayRepository: FakeGatewayRepository::withGateways([$gateway]),
        clock: makeResolvePaymentLinkClock(),
    );

    $resolved = $useCase->execute($booking->reference->toString());

    expect($transactionRepository->findByExternalId('tr_old_expired')?->status)->toBe(TransactionStatus::EXPIRED)
        ->and($resolved->externalId)->toBe('tr_new_payment')
        ->and($transactionRepository->findByExternalId('tr_new_payment'))->not->toBeNull();
});

test('throws when the booking already has a paid online transaction', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $booking = makePaidOnlineBooking();
    $bookingId = $bookingRepository->save($booking);
    $booking = $booking->withId($bookingId);

    $paid = Transaction::forPaymentService(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        externalId: 'tr_paid_existing',
        checkoutUrl: Uri::parse('https://checkout.example.test/paid'),
        gateway: 'mollie',
        gatewayUrl: Uri::parse('https://gateway.example.test/pay/tr_paid_existing'),
        expiresAt: new DateTimeImmutable('2026-03-21 12:00:00'),
    )->complete()->withCreatedAt(new DateTimeImmutable('2026-03-18 11:00:00'));

    $transactionRepository = FakeTransactionRepository::withTransactions($paid);
    $gateway = new FakePaymentGateway(
        id: 'mollie',
        verifyPaymentUsing: static fn(Transaction $transaction): Transaction => $transaction,
    );

    $useCase = new ResolveBookingPaymentLink(
        bookingRepository: $bookingRepository,
        transactionRepository: $transactionRepository,
        gatewayRepository: FakeGatewayRepository::withGateways([$gateway]),
        clock: makeResolvePaymentLinkClock(),
    );

    expect(fn() => $useCase->execute($booking->reference->toString()))
        ->toThrow(DomainException::class, 'Booking has already been paid.');
});
