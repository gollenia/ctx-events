<?php

declare(strict_types=1);

use Contexis\Events\Booking\Application\Services\SynchronizeBookingPaymentBalance;
use Contexis\Events\Booking\Application\Services\CancelOpenBookingTransactions;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Tests\Support\FakeBookingEmailTrigger;
use Tests\Support\FakeBookingRepository;
use Tests\Support\FakeCurrentActorProvider;
use Tests\Support\FakeGatewayRepository;
use Tests\Support\FakePaymentGateway;
use Tests\Support\FakeTransactionRepository;
use Uri\Rfc3986\Uri;

function makePaymentBalanceBooking(int $amountCents = 5000): Booking
{
    $currency = Currency::fromCode('EUR');

    return new Booking(
        reference: BookingReference::fromString('BOOK-BALANCE-1'),
        email: new Email('balance@example.test'),
        name: PersonName::from('Erika', 'Muster'),
        priceSummary: PriceSummary::fromValues(Price::from($amountCents, $currency), Price::from(0, $currency), Price::from(0, $currency)),
        bookingTime: new DateTimeImmutable('2026-03-18 10:00:00'),
        status: BookingStatus::PENDING,
        registration: new RegistrationData(['email' => 'balance@example.test', 'first_name' => 'Erika', 'last_name' => 'Muster']),
        attendees: AttendeeCollection::empty(),
        gateway: 'mollie',
        coupon: null,
        transactions: null,
        eventId: EventId::from(1),
    );
}

function makePaymentBalanceSynchronizer(
    FakeBookingRepository $bookings,
    FakeTransactionRepository $transactions,
    FakeBookingEmailTrigger $emails,
    FakePaymentGateway $gateway,
): SynchronizeBookingPaymentBalance {
    $clock = Mockery::mock(Clock::class);
    $clock->allows('now')->andReturn(new DateTimeImmutable('2026-03-18 12:00:00'));

    return new SynchronizeBookingPaymentBalance(
        transactionRepository: $transactions,
        gatewayRepository: FakeGatewayRepository::withGateways([$gateway]),
        bookingRepository: $bookings,
        bookingEmailTrigger: $emails,
        clock: $clock,
        currentActorProvider: new FakeCurrentActorProvider(),
    );
}

test('a price increase creates an additional Mollie payment and mails its link', function () {
    $bookings = FakeBookingRepository::empty();
    $booking = makePaymentBalanceBooking();
    $bookingId = $bookings->save($booking);
    $before = $booking->withId($bookingId);
    $after = $before->withPriceSummary(PriceSummary::fromValues(
        Price::from(7000, Currency::fromCode('EUR')),
        Price::from(0, Currency::fromCode('EUR')),
        Price::from(0, Currency::fromCode('EUR')),
    ));
    $original = Transaction::forPaymentService(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        externalId: 'tr_original',
        checkoutUrl: Uri::parse('https://checkout.example.test/original'),
        gateway: 'mollie',
        gatewayUrl: Uri::parse('https://gateway.example.test/original'),
    );
    $transactions = FakeTransactionRepository::withTransactions($original);
    $emails = new FakeBookingEmailTrigger();
    $gateway = new FakePaymentGateway(
        id: 'mollie',
        initiatePaymentUsing: static fn(Booking $booking, ?Price $amount): Transaction => Transaction::forPaymentService(
            bookingId: $booking->id,
            amount: $amount ?? throw new RuntimeException('Expected additional amount.'),
            externalId: 'tr_additional',
            checkoutUrl: Uri::parse('https://checkout.example.test/additional'),
            gateway: 'mollie',
            gatewayUrl: Uri::parse('https://gateway.example.test/additional'),
        ),
    );

    makePaymentBalanceSynchronizer($bookings, $transactions, $emails, $gateway)->execute($before, $after);

    expect($transactions->findByExternalId('tr_additional')?->amount->amountCents)->toBe(2000)
        ->and($emails->lastCall()['trigger'] ?? null)->toBe(EmailTrigger::BOOKING_PAYMENT_LINK);
});

test('a price reduction records an external refund instead of creating a negative transaction', function () {
    $bookings = FakeBookingRepository::empty();
    $booking = makePaymentBalanceBooking();
    $bookingId = $bookings->save($booking);
    $before = $booking->withId($bookingId);
    $after = $before->withPriceSummary(PriceSummary::fromValues(
        Price::from(4000, Currency::fromCode('EUR')),
        Price::from(0, Currency::fromCode('EUR')),
        Price::from(0, Currency::fromCode('EUR')),
    ));
    $paid = Transaction::forPaymentService(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        externalId: 'tr_paid',
        checkoutUrl: Uri::parse('https://checkout.example.test/paid'),
        gateway: 'mollie',
        gatewayUrl: Uri::parse('https://gateway.example.test/paid'),
    )->complete();
    $transactions = FakeTransactionRepository::withTransactions($paid);
    $emails = new FakeBookingEmailTrigger();
    $gateway = new FakePaymentGateway(id: 'mollie');

    makePaymentBalanceSynchronizer($bookings, $transactions, $emails, $gateway)->execute($before, $after);

    $stored = $bookings->find($bookingId);
    expect($transactions->findByExternalId('tr_paid')?->amount->amountCents)->toBe(5000)
        ->and(array_map(
            static fn($entry): ?string => $entry->message,
            $stored?->logEntries?->toArray() ?? [],
        ))->toContain('The booking total was reduced. Refund 10.00 externally.')
        ->and($emails->lastCall())->toBeNull();
});

test('cancelling a booking closes only its open payment requests', function () {
    $bookings = FakeBookingRepository::empty();
    $booking = makePaymentBalanceBooking();
    $bookingId = $bookings->save($booking);
    $pending = Transaction::forPaymentService(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        externalId: 'tr_open',
        checkoutUrl: Uri::parse('https://checkout.example.test/open'),
        gateway: 'mollie',
        gatewayUrl: Uri::parse('https://gateway.example.test/open'),
    );
    $paid = Transaction::forPaymentService(
        bookingId: $bookingId,
        amount: Price::from(1000, Currency::fromCode('EUR')),
        externalId: 'tr_paid',
        checkoutUrl: Uri::parse('https://checkout.example.test/paid'),
        gateway: 'mollie',
        gatewayUrl: Uri::parse('https://gateway.example.test/paid'),
    )->complete();
    $transactions = FakeTransactionRepository::withTransactions($pending, $paid);
    $gateway = new FakePaymentGateway(id: 'mollie');

    (new CancelOpenBookingTransactions(
        transactionRepository: $transactions,
        gatewayRepository: FakeGatewayRepository::withGateways([$gateway]),
    ))->execute($booking->withId($bookingId));

    expect($transactions->findByExternalId('tr_open')?->status->name)->toBe('CANCELED')
        ->and($transactions->findByExternalId('tr_paid')?->status->name)->toBe('PAID');
});
