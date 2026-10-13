<?php

declare(strict_types=1);

use Contexis\Events\Booking\Application\UseCases\ResolveBookingPaymentLink;
use Contexis\Events\Booking\Domain\AttendeeCollection;
use Contexis\Events\Booking\Domain\Booking;
use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\BookingStatus;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Communication\Application\UseCases\ResolveEmailPaymentInformation;
use Contexis\Events\Communication\Application\DTOs\TriggeredEmailContext;
use Contexis\Events\Communication\Infrastructure\TiptapDocumentRenderer;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Payment\Application\Contracts\PaymentQrGenerator;
use Contexis\Events\Payment\Domain\Transaction;
use Contexis\Events\Payment\Domain\ValueObjects\BankData;
use Contexis\Events\Shared\Domain\Contracts\Clock;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Tests\Support\FakeBookingRepository;
use Tests\Support\FakeGatewayRepository;
use Tests\Support\FakePaymentGateway;
use Tests\Support\FakeTransactionRepository;
use Tests\Support\FakeEventFactory;

function makeEmailPaymentInformationBooking(string $gateway = 'offline'): Booking
{
    return new Booking(
        reference: BookingReference::fromString('BOOK-PAYMENT-1'),
        email: new Email('payment@example.test'),
        name: PersonName::from('Erika', 'Muster'),
        priceSummary: PriceSummary::fromValues(
            bookingPrice: Price::from(5000, Currency::fromCode('EUR')),
            donationAmount: Price::from(0, Currency::fromCode('EUR')),
            discountAmount: Price::from(0, Currency::fromCode('EUR')),
        ),
        bookingTime: new DateTimeImmutable('2026-03-18 10:00:00'),
        status: BookingStatus::PENDING,
        registration: new RegistrationData([]),
        attendees: AttendeeCollection::empty(),
        gateway: $gateway,
        coupon: null,
        transactions: null,
        eventId: EventId::from(1),
    );
}

function makeEmailPaymentInformationClock(): Clock
{
    $clock = Mockery::mock(Clock::class);
    $clock->allows('now')->andReturn(new DateTimeImmutable('2026-03-18 12:00:00'));

    return $clock;
}

function makePaymentInformationResolver(
    FakeBookingRepository $bookingRepository,
    FakeTransactionRepository $transactionRepository,
    FakeGatewayRepository $gatewayRepository,
    Clock $clock,
): ResolveEmailPaymentInformation {
    return new ResolveEmailPaymentInformation(
        transactionRepository: $transactionRepository,
        gatewayRepository: $gatewayRepository,
        resolveBookingPaymentLink: new ResolveBookingPaymentLink(
            bookingRepository: $bookingRepository,
            transactionRepository: $transactionRepository,
            gatewayRepository: $gatewayRepository,
            clock: $clock,
        ),
        paymentQrGenerator: new class implements PaymentQrGenerator {
            public function generate(Transaction $transaction, string $reference, string $format = 'svg'): string
            {
                return 'data:image/png;base64,' . base64_encode('png-payment-qr');
            }

            public function mimeType(string $format): string
            {
                return 'image/png';
            }
        },
        clock: $clock,
    );
}

test('resolves pending offline payment data and its QR image for an email', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeEmailPaymentInformationBooking());
    $booking = makeEmailPaymentInformationBooking()->withId($bookingId);
    $transactionRepository = FakeTransactionRepository::withTransactions(Transaction::forBankTransfer(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        gateway: 'offline',
        bankData: BankData::fromValues('Example GmbH', 'AT611904300234573201', 'BKAUATWW', 'Example Bank'),
        expiresAt: new DateTimeImmutable('2026-03-20 12:00:00'),
    ));
    $gatewayRepository = FakeGatewayRepository::withGateways([new FakePaymentGateway(id: 'offline')]);
    $clock = makeEmailPaymentInformationClock();

    $payment = makePaymentInformationResolver(
        $bookingRepository,
        $transactionRepository,
        $gatewayRepository,
        $clock,
    )->execute($booking);

    expect($payment)->not->toBeNull()
        ->and($payment->gatewayTitle)->toBe('Fake')
        ->and($payment->transaction->bankData?->iban)->toBe('AT611904300234573201')
        ->and($payment->qrCodePng)->toBe('png-payment-qr');
});

test('does not render expired offline payment information', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeEmailPaymentInformationBooking());
    $booking = makeEmailPaymentInformationBooking()->withId($bookingId);
    $transactionRepository = FakeTransactionRepository::withTransactions(Transaction::forBankTransfer(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        gateway: 'offline',
        bankData: BankData::fromValues('Example GmbH', 'AT611904300234573201', 'BKAUATWW', 'Example Bank'),
        expiresAt: new DateTimeImmutable('2026-03-17 12:00:00'),
    ));
    $gatewayRepository = FakeGatewayRepository::withGateways([new FakePaymentGateway(id: 'offline')]);
    $clock = makeEmailPaymentInformationClock();

    $payment = makePaymentInformationResolver(
        $bookingRepository,
        $transactionRepository,
        $gatewayRepository,
        $clock,
    )->execute($booking);

    expect($payment)->toBeNull();
});

test('renders the payment information mail block with an inline QR image', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $bookingId = $bookingRepository->save(makeEmailPaymentInformationBooking());
    $booking = makeEmailPaymentInformationBooking()->withId($bookingId);
    $transactionRepository = FakeTransactionRepository::withTransactions(Transaction::forBankTransfer(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        gateway: 'offline',
        bankData: BankData::fromValues('Example GmbH', 'AT611904300234573201', 'BKAUATWW', 'Example Bank'),
    ));
    $gatewayRepository = FakeGatewayRepository::withGateways([new FakePaymentGateway(id: 'offline')]);
    $clock = makeEmailPaymentInformationClock();
    $renderer = new TiptapDocumentRenderer(makePaymentInformationResolver(
        $bookingRepository,
        $transactionRepository,
        $gatewayRepository,
        $clock,
    ));

    $rendered = $renderer->render(
        json_encode([
            'type' => 'doc',
            'content' => [['type' => 'paymentInformation']],
        ], JSON_THROW_ON_ERROR),
        new TriggeredEmailContext(
            booking: $booking,
            event: FakeEventFactory::create(1),
            attendees: AttendeeCollection::empty(),
            transactions: \Contexis\Events\Payment\Domain\TransactionCollection::empty(),
        ),
    );

    expect($rendered->html)->toContain('Example GmbH', 'AT611904300234573201', 'cid:ctx-payment-qr-')
        ->and($rendered->inlineAttachments)->toHaveCount(1)
        ->and($rendered->inlineAttachments[0]->content)->toBe('png-payment-qr');
});

test('renders a durable booking payment link instead of the Mollie checkout URL', function () {
    $bookingRepository = FakeBookingRepository::empty();
    $booking = makeEmailPaymentInformationBooking('mollie');
    $bookingId = $bookingRepository->save($booking);
    $booking = $booking->withId($bookingId);
    $transaction = Transaction::forPaymentService(
        bookingId: $bookingId,
        amount: Price::from(5000, Currency::fromCode('EUR')),
        externalId: 'tr_email_payment',
        checkoutUrl: \Uri\Rfc3986\Uri::parse('https://www.mollie.com/checkout/short-lived'),
        gateway: 'mollie',
        gatewayUrl: \Uri\Rfc3986\Uri::parse('https://www.mollie.com/dashboard/payments/tr_email_payment'),
        expiresAt: new DateTimeImmutable('2026-03-20 12:00:00'),
    );
    $transactionRepository = FakeTransactionRepository::withTransactions($transaction);
    $gatewayRepository = FakeGatewayRepository::withGateways([
        new FakePaymentGateway(
            id: 'mollie',
            verifyPaymentUsing: static fn(Transaction $payment): Transaction => $payment,
        ),
    ]);
    $renderer = new TiptapDocumentRenderer(makePaymentInformationResolver(
        $bookingRepository,
        $transactionRepository,
        $gatewayRepository,
        makeEmailPaymentInformationClock(),
    ));

    $rendered = $renderer->render(
        json_encode([
            'type' => 'doc',
            'content' => [['type' => 'paymentInformation']],
        ], JSON_THROW_ON_ERROR),
        new TriggeredEmailContext(
            booking: $booking,
            event: FakeEventFactory::create(1),
            attendees: AttendeeCollection::empty(),
            transactions: \Contexis\Events\Payment\Domain\TransactionCollection::empty(),
        ),
    );

    expect($rendered->html)
        ->toContain('https://events.example.test/?ctx_events_booking_payment=BOOK-PAYMENT-1')
        ->not->toContain('www.mollie.com/checkout/short-lived');
});

if (!function_exists('site_url')) {
    function site_url(string $path = ''): string
    {
        return 'https://events.example.test' . $path;
    }
}

if (!function_exists('add_query_arg')) {
    function add_query_arg(string|array $key, ?string $value = null, ?string $url = null): string
    {
        if (!is_string($key) || $url === null) {
            throw new RuntimeException('Unsupported test query arguments.');
        }

        return $url . '?' . rawurlencode($key) . '=' . rawurlencode($value ?? '');
    }
}
